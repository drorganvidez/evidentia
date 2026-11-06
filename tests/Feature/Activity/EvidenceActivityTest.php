<?php

namespace Tests\Feature\Activity;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Proof;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private Committee $programa;

    private Committee $logistica;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate(['upload_evidences_timestamp' => now()->addWeek(), 'secret' => 'salt']);
        $this->programa = Committee::create(['name' => 'Programa']);
        $this->logistica = Committee::create(['name' => 'Logística']);
        $this->student = User::factory()->create(['username' => 'anagar']);
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $this->actingAs($this->student)->withSession(['_token' => 'upload-session']);
    }

    public function test_a_new_draft_records_each_proof_and_then_the_evidence(): void
    {
        Storage::put('tmp/anagar/upload-session/certificado.pdf', '%PDF-1.4');
        Storage::put('tmp/anagar/upload-session/fotos.zip', 'zip');

        $this->post(route('evidence.draft'), $this->form())->assertRedirect(route('evidence.list'));

        $evidence = Evidence::sole();
        $this->assertSame(['proof.uploaded', 'proof.uploaded', 'evidence.created'], ActivityLog::orderBy('id')->pluck('action')->all());
        $this->assertEqualsCanonicalizing(
            [['file' => 'certificado.pdf', 'size' => 8], ['file' => 'fotos.zip', 'size' => 3]],
            ActivityLog::where('action', 'proof.uploaded')->get()->pluck('details')->all(),
        );

        $log = ActivityLog::where('action', 'evidence.created')->sole();
        $this->assertSame('STUDENT', $log->acting_role);
        $this->assertSame($evidence->id, $log->subject_root_id);
        $this->assertNull($log->committee_id, 'A draft never reaches its committee');
        $this->assertSame('DRAFT', $log->details['status']);
        $this->assertSame(1, $log->details['version']);
        $this->assertEquals(2.5, $log->details['hours']);
        $this->assertEqualsCanonicalizing(['certificado.pdf', 'fotos.zip'], $log->details['files']);
        $this->assertSame(1, ActivityLog::distinct()->count('request_id'));
        Storage::assertMissing('tmp/anagar/upload-session/certificado.pdf');
    }

    public function test_publishing_an_edit_records_the_version_and_only_what_changed(): void
    {
        $draft = $this->evidence('DRAFT');

        $this->post(route('evidence.publish.edit'), ['_id' => $draft->id, 'committee' => $this->logistica->id] + $this->form())
            ->assertRedirect(route('evidence.list'));

        $published = Evidence::where('last', true)->sole();
        $log = ActivityLog::sole();
        $this->assertSame('evidence.published', $log->action);
        $this->assertSame($published->id, $log->subject_id);
        $this->assertSame($draft->id, $log->subject_root_id);
        $this->assertSame($this->logistica->id, $log->committee_id);
        $this->assertSame('PENDING', $log->details['status']);
        $this->assertSame($draft->id, $log->details['previous_id']);
        $this->assertSame(2, $log->details['version']);
        $this->assertEquals(
            ['before' => ['hours' => 1.0, 'committee_id' => $this->programa->id, 'committee_name' => 'Programa'],
                'after' => ['hours' => 2.5, 'committee_id' => $this->logistica->id, 'committee_name' => 'Logística']],
            ['before' => $log->details['before'], 'after' => $log->details['after']],
        );
        $this->assertArrayNotHasKey('automatic', $log->details);
        $this->assertFalse($log->flagged);
    }

    public function test_saving_a_draft_without_changes_records_no_before_and_after(): void
    {
        $draft = $this->evidence('DRAFT', hours: 2.5);

        $this->post(route('evidence.draft.edit'), ['_id' => $draft->id] + $this->form());

        $log = ActivityLog::sole();
        $this->assertSame('evidence.edited', $log->action);
        $this->assertArrayNotHasKey('before', $log->details);
        $this->assertArrayNotHasKey('after', $log->details);
        $this->assertSame([], $log->details['files']);
    }

    public function test_an_evidence_the_president_accepts_by_sending_it_is_flagged(): void
    {
        $president = User::factory()->create();
        $president->roles()->attach(Role::forceCreate(['rol' => 'PRESIDENT', 'slug' => 'president']));

        $this->actingAs($president)->post(route('evidence.publish'), $this->form())->assertRedirect(route('evidence.list'));

        $log = ActivityLog::sole();
        $this->assertSame('evidence.published', $log->action);
        $this->assertSame('STUDENT', $log->acting_role);
        $this->assertSame('ACCEPTED', $log->details['status']);
        $this->assertTrue($log->details['automatic']);
        $this->assertSame(['role_overlap'], $log->flags);
        $this->assertTrue($log->flagged);
    }

    public function test_reopening_a_rejected_evidence_records_it_outside_the_committee(): void
    {
        $rejected = $this->evidence('REJECTED');

        $this->post(route('evidence.reedit'), ['_id' => $rejected->id])->assertRedirect(route('evidence.list'));

        $log = ActivityLog::sole();
        $this->assertSame('evidence.reedited', $log->action);
        $this->assertSame(['before' => 'REJECTED', 'after' => 'DRAFT'], $log->details);
        $this->assertNull($log->committee_id);
    }

    public function test_removing_an_evidence_records_every_proof_and_deletes_its_folders_after_the_commit(): void
    {
        $first = $this->evidence('DRAFT', proof: 'certificado');
        $second = $this->evidence('PENDING', proof: 'certificado', previous: $first);

        $this->post(route('evidence.remove'), ['_id' => $second->id])->assertRedirect(route('evidence.list'));

        $this->assertSame(0, Evidence::count());
        $this->assertSame(['proof.deleted', 'proof.deleted', 'evidence.deleted'], ActivityLog::orderBy('id')->pluck('action')->all());
        $this->assertSame([$first->id], ActivityLog::distinct()->pluck('subject_root_id')->all());

        $log = ActivityLog::where('action', 'evidence.deleted')->sole();
        $this->assertSame('Organización de mesas', $log->subject_title);
        $this->assertSame($this->programa->id, $log->committee_id);
        $this->assertSame(['versions' => 2, 'files' => ['certificado.pdf', 'certificado.pdf'], 'hours' => 1], $log->details);
        Storage::assertMissing('proofs/anagar/evidence_'.$first->id.'/certificado.pdf');
        Storage::assertMissing('proofs/anagar/evidence_'.$second->id.'/certificado.pdf');
    }

    public function test_a_removal_that_fails_keeps_the_rows_and_the_files(): void
    {
        $evidence = $this->evidence('PENDING', proof: 'certificado');
        Activity::fake()->failOn(ActivityAction::EvidenceDeleted);

        $this->withoutExceptionHandling();
        try {
            $this->post(route('evidence.remove'), ['_id' => $evidence->id]);
            $this->fail('The annotation should have failed');
        } catch (\RuntimeException) {
        }

        $this->assertNotNull($evidence->fresh());
        $this->assertSame(1, Proof::count());
        Storage::assertExists('proofs/anagar/evidence_'.$evidence->id.'/certificado.pdf');
    }

    public function test_a_proof_that_cannot_be_stored_leaves_neither_rows_nor_annotations(): void
    {
        Storage::put('tmp/anagar/upload-session/certificado.pdf', '%PDF-1.4');
        File::creating(fn () => throw new \RuntimeException('Disco lleno'));

        $this->post(route('evidence.publish'), $this->form())
            ->assertSessionHas('error', 'No se pudo guardar una de las pruebas. Vuelve a intentarlo.');

        $this->assertSame(0, Evidence::count());
        $this->assertSame(0, ActivityLog::count());
    }

    private function evidence(string $status, float $hours = 1, ?string $proof = null, ?Evidence $previous = null): Evidence
    {
        $previous?->update(['last' => false]);
        $evidence = Evidence::create([
            'title' => 'Organización de mesas', 'description' => 'Organicé las mesas del congreso.', 'hours' => $hours,
            'status' => $status, 'user_id' => $this->student->id, 'committee_id' => $this->programa->id,
            'points_to' => $previous?->id,
        ]);
        if ($proof !== null) {
            $route = 'proofs/anagar/evidence_'.$evidence->id.'/'.$proof.'.pdf';
            Storage::put($route, '%PDF-1.4');
            $file = File::create(['name' => $proof, 'type' => 'pdf', 'route' => $route, 'size' => 8]);
            Proof::create(['evidence_id' => $evidence->id, 'file_id' => $file->id]);
        }

        return $evidence;
    }

    private function form(): array
    {
        return [
            'title' => 'Organización de mesas',
            'hours' => 2,
            'minutes' => 30,
            'committee' => $this->programa->id,
            'description' => 'Organicé las mesas del congreso durante dos horas.',
        ];
    }
}
