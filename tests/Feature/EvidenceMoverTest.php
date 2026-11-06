<?php

namespace Tests\Feature;

use App\Enums\Deadline;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Proof;
use App\Models\ReasonRejection;
use App\Models\Role;
use App\Models\User;
use App\Services\Deadlines;
use App\Services\EvidenceMover;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class EvidenceMoverTest extends TestCase
{
    use RefreshDatabase;

    private Committee $logistica;

    private Committee $programa;

    private User $student;

    private User $lecturer;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate([
            'id' => 1, 'upload_evidences_timestamp' => now()->addWeek(), 'validate_evidences_timestamp' => now()->addWeek(), 'secret' => 'salt',
        ]);
        $this->logistica = Committee::create(['name' => 'Logística']);
        $this->programa = Committee::create(['name' => 'Programa']);
        $this->student = User::factory()->create();
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $this->lecturer = User::factory()->create();
        $this->lecturer->roles()->attach(Role::forceCreate(['rol' => 'LECTURE', 'slug' => 'lecture']));
    }

    private function evidence(string $status, array $attributes = []): Evidence
    {
        $evidence = Evidence::create([
            'title' => 'Organización de mesas', 'description' => '<p>Mesas.</p>', 'hours' => 2, 'status' => $status,
            'user_id' => $this->student->id, 'committee_id' => $this->logistica->id, ...$attributes,
        ]);
        \Stamp::compute_evidence($evidence)->save();

        return $evidence->fresh();
    }

    private function mover(): EvidenceMover
    {
        return app(EvidenceMover::class);
    }

    public function test_it_moves_the_last_version_without_a_new_row_and_keeps_everything_else(): void
    {
        $first = $this->evidence('REJECTED', ['last' => false]);
        ReasonRejection::create(['reason' => 'Falta el certificado.', 'evidence_id' => $first->id]);
        $evidence = $this->evidence('REJECTED', ['points_to' => $first->id]);
        $rejection = ReasonRejection::create(['reason' => 'Las horas no cuadran.', 'evidence_id' => $evidence->id]);
        $file = File::create(['name' => 'certificado', 'type' => 'pdf', 'route' => 'proofs/certificado.pdf', 'size' => 8]);
        $proof = Proof::create(['evidence_id' => $evidence->id, 'file_id' => $file->id]);
        $this->actingAs($this->student);

        $moved = $this->mover()->move($evidence, $this->programa, 'STUDENT');

        $this->assertSame(2, Evidence::count());
        $this->assertSame($evidence->id, $moved->id);
        $fresh = $evidence->fresh();
        $this->assertSame($this->programa->id, $fresh->committee_id);
        $this->assertSame('REJECTED', $fresh->status);
        $this->assertTrue((bool) $fresh->last);
        $this->assertSame($first->id, $fresh->points_to);
        $this->assertTrue($fresh->integrity());
        $this->assertSame('Las horas no cuadran.', $fresh->reasonRejection->reason);
        $this->assertSame($rejection->id, $fresh->reasonRejection->id);
        $this->assertSame([$proof->id], $fresh->proofs->pluck('id')->all());
        // la versión anterior conserva su comité histórico
        $this->assertSame($this->logistica->id, $first->fresh()->committee_id);
    }

    public function test_the_annotation_keeps_before_after_and_the_committee_that_lost_the_evidence(): void
    {
        $evidence = $this->evidence('PENDING');
        $this->actingAs($this->lecturer);

        $this->mover()->move($evidence, $this->programa, 'LECTURE', checkDeadline: true);

        $log = ActivityLog::sole();
        $this->assertSame('evidence.moved', $log->action);
        $this->assertSame('LECTURE', $log->acting_role);
        $this->assertSame($this->logistica->id, $log->committee_id);
        $this->assertSame('Logística', $log->committee_name);
        $this->assertSame($this->student->id, $log->target_user_id);
        $this->assertEquals([
            'before' => ['committee_id' => $this->logistica->id, 'committee_name' => 'Logística'],
            'after' => ['committee_id' => $this->programa->id, 'committee_name' => 'Programa'],
            'status' => 'PENDING',
        ], $log->details);
        $this->assertNull($log->flags);
    }

    public function test_the_author_moving_it_is_not_its_own_target_and_a_draft_has_no_committee(): void
    {
        $draft = $this->evidence('DRAFT');
        $this->actingAs($this->student);

        $this->mover()->move($draft, $this->programa, 'STUDENT');

        $log = ActivityLog::sole();
        $this->assertNull($log->target_user_id);
        $this->assertNull($log->committee_id);
        $this->assertSame('STUDENT', $log->acting_role);
    }

    public function test_the_governing_deadline_follows_the_status(): void
    {
        $this->assertSame(Deadline::UploadEvidences, EvidenceMover::governingDeadline(new Evidence(['status' => 'DRAFT'])));
        $this->assertSame(Deadline::UploadEvidences, EvidenceMover::governingDeadline(new Evidence(['status' => 'REJECTED'])));
        $this->assertSame(Deadline::ValidateEvidences, EvidenceMover::governingDeadline(new Evidence(['status' => 'PENDING'])));
        $this->assertSame(Deadline::ValidateEvidences, EvidenceMover::governingDeadline(new Evidence(['status' => 'ACCEPTED'])));
    }

    public function test_the_management_is_flagged_when_the_deadline_of_the_status_is_closed(): void
    {
        Configuration::first()->update(['upload_evidences_timestamp' => now()->subDay()]);
        app(Deadlines::class)->refresh();
        $rejected = $this->evidence('REJECTED');
        $pending = $this->evidence('PENDING');
        $this->actingAs($this->lecturer);

        $this->mover()->move($rejected, $this->programa, 'LECTURE', checkDeadline: true);
        $this->mover()->move($pending, $this->programa, 'LECTURE', checkDeadline: true);

        $this->assertSame(['after_deadline'], ActivityLog::where('subject_id', $rejected->id)->sole()->flags);
        $this->assertNull(ActivityLog::where('subject_id', $pending->id)->sole()->flags);
    }

    public function test_without_checking_the_deadline_there_is_no_flag(): void
    {
        Configuration::first()->update(['upload_evidences_timestamp' => now()->subDay()]);
        app(Deadlines::class)->refresh();
        $this->actingAs($this->student);

        $this->mover()->move($this->evidence('REJECTED'), $this->programa, 'STUDENT');

        $this->assertNull(ActivityLog::sole()->flags);
    }

    public function test_it_refuses_the_same_committee(): void
    {
        $evidence = $this->evidence('PENDING');
        $this->actingAs($this->student);

        try {
            $this->mover()->move($evidence, $this->logistica);
            $this->fail('The evidence was moved to its own committee.');
        } catch (DomainException $e) {
            $this->assertSame('Elige un comité distinto del actual.', $e->getMessage());
        }
        $this->assertSame(0, ActivityLog::count());
    }

    public function test_it_refuses_a_version_that_is_not_the_last(): void
    {
        $old = $this->evidence('REJECTED', ['last' => false]);
        $this->actingAs($this->student);

        $this->expectException(LogicException::class);
        $this->mover()->move($old, $this->programa);
    }
}
