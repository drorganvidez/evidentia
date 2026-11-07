<?php

namespace Tests\Feature;

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
use ZipArchive;

class EvidenceArchiveTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $other;

    private User $lecturer;

    private Committee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addWeek(),
            'validate_evidences_timestamp' => now()->addWeeks(2),
        ]);
        $this->committee = Committee::create(['name' => 'Programa']);
        $role = Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']);
        $this->student = User::factory()->create(['username' => 'alumno1', 'surname' => 'Pérez']);
        $this->student->roles()->attach($role);
        $this->other = User::factory()->create(['username' => 'alumno2']);
        $this->other->roles()->attach($role);
        $this->lecturer = User::factory()->create();
        $this->lecturer->roles()->attach(Role::forceCreate(['rol' => 'LECTURE', 'slug' => 'lecture']));
    }

    public function test_the_management_copy_holds_every_sent_evidence_with_its_proofs_but_no_draft(): void
    {
        $sent = $this->evidence($this->student, 'ACCEPTED', 'Mesas', 'acta');
        $this->evidence($this->other, 'PENDING', 'Carteles', 'cartel');
        $this->evidence($this->student, 'DRAFT', 'Borrador secreto', 'nota');

        $response = $this->actingAs($this->lecturer)->get(route('lecture.export.archive'));

        $response->assertOk();
        $this->assertStringContainsString('evidencias-', $response->headers->get('Content-Disposition'));
        [$csv, $files] = $this->read($response->getFile()->getPathname());
        $this->assertStringContainsString('Mesas', $csv);
        $this->assertStringContainsString('Carteles', $csv);
        $this->assertStringContainsString('Aceptada', $csv);
        $this->assertStringNotContainsString('Borrador secreto', $csv);
        $this->assertContains('pruebas/alumno1/'.$sent->id.'/acta-'.$sent->proofs->first()->id.'.pdf', $files);
        $this->assertCount(2, array_filter($files, fn ($name) => str_starts_with($name, 'pruebas/')));
        $this->assertSame(1, ActivityLog::where('action', 'export.archive')->count());
    }

    public function test_a_student_copies_only_their_own_evidences_drafts_included(): void
    {
        $this->evidence($this->student, 'DRAFT', 'Mi borrador', 'nota');
        $this->evidence($this->student, 'REJECTED', 'Mi rechazada', 'acta');
        $this->evidence($this->other, 'ACCEPTED', 'Ajena', 'cartel');

        $response = $this->actingAs($this->student)->get(route('evidence.archive'));

        $response->assertOk();
        [$csv, $files] = $this->read($response->getFile()->getPathname());
        $this->assertStringContainsString('Mi borrador', $csv);
        $this->assertStringContainsString('Mi rechazada', $csv);
        $this->assertStringNotContainsString('Ajena', $csv);
        $this->assertEmpty(array_filter($files, fn ($name) => str_starts_with($name, 'pruebas/alumno2')));
        $this->assertSame(1, ActivityLog::where('action', 'export.archive')->count());
    }

    public function test_only_the_management_downloads_the_copy_of_the_course(): void
    {
        $this->actingAs($this->student)->get(route('lecture.export.archive'))->assertNotFound();
        $this->actingAs($this->student)->get(route('president.export.archive'))->assertNotFound();
    }

    public function test_both_pages_offer_the_copy(): void
    {
        $this->actingAs($this->student)->get(route('evidence.list'))->assertOk()->assertSee('evidence-archive', false);
        $this->actingAs($this->lecturer)->get(route('lecture.export'))->assertOk()
            ->assertSee(route('lecture.export.archive'), false);
    }

    private function evidence(User $user, string $status, string $title, string $proof): Evidence
    {
        $evidence = Evidence::create([
            'title' => $title,
            'description' => '<p>Descripción de '.$title.'</p>',
            'hours' => 2,
            'status' => $status,
            'user_id' => $user->id,
            'committee_id' => $this->committee->id,
            'last' => true,
        ]);
        $route = 'proofs/'.$proof.'-'.$evidence->id.'.pdf';
        Storage::put($route, '%PDF-1.4');
        $file = File::create(['name' => $proof, 'type' => 'pdf', 'route' => $route, 'size' => 8]);
        Proof::create(['evidence_id' => $evidence->id, 'file_id' => $file->id]);

        return $evidence;
    }

    /** @return array{0: string, 1: list<string>} */
    private function read(string $path): array
    {
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $files[] = $zip->getNameIndex($i);
        }
        $csv = (string) $zip->getFromName('evidencias.csv');
        $zip->close();

        return [$csv, $files];
    }
}
