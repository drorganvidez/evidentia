<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Proof;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoordinatorReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $coordinator;

    private Committee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addWeek(),
            'validate_evidences_timestamp' => now()->addWeeks(2),
        ]);
        $this->committee = Committee::create(['name' => 'Programa']);
        $this->student = User::factory()->create();
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $this->coordinator = User::factory()->create();
        $this->coordinator->roles()->attach(Role::forceCreate(['rol' => 'COORDINATOR', 'slug' => 'coordinator']));
        Coordinator::forceCreate(['user_id' => $this->coordinator->id, 'committee_id' => $this->committee->id]);
    }

    public function test_the_coordinator_accepts_a_pending_evidence(): void
    {
        $evidence = $this->evidence('PENDING');

        $this->actingAs($this->coordinator)
            ->post(route('coordinator.evidence.accept', ['id' => $evidence->id]))
            ->assertRedirect(route('coordinator.evidence.list.accepted'));

        $this->assertSame('ACCEPTED', $evidence->fresh()->status);
    }

    public function test_opening_a_link_does_not_accept_an_evidence(): void
    {
        $evidence = $this->evidence('PENDING');

        $this->actingAs($this->coordinator)->get('/coordinator/evidence/accept/'.$evidence->id);

        $this->assertSame('PENDING', $evidence->fresh()->status);
    }

    public function test_the_coordinator_cannot_see_or_review_a_draft(): void
    {
        $draft = $this->evidence('DRAFT');
        $this->actingAs($this->coordinator);

        $this->get(route('coordinator.evidence.view', ['id' => $draft->id]))->assertNotFound();
        $this->get(route('proof.download', ['id' => $this->proofOf($draft)->id]))->assertNotFound();
        $this->post(route('coordinator.evidence.accept', ['id' => $draft->id]))->assertNotFound();
        $this->post(route('coordinator.evidence.reject'), ['_id' => $draft->id, 'reasonrejection' => 'Falta el certificado.'])->assertNotFound();

        $this->assertSame('DRAFT', $draft->fresh()->status);
    }

    public function test_the_coordinator_cannot_accept_a_version_the_student_replaced(): void
    {
        $old = $this->evidence('REJECTED');
        $old->update(['last' => false]);
        $new = $this->evidence('PENDING');
        $new->update(['points_to' => $old->id]);

        $this->actingAs($this->coordinator)
            ->post(route('coordinator.evidence.accept', ['id' => $old->id]))
            ->assertNotFound();

        $this->assertSame('REJECTED', $old->fresh()->status);
    }

    public function test_rejecting_needs_a_reason(): void
    {
        $evidence = $this->evidence('PENDING');

        $this->actingAs($this->coordinator)
            ->post(route('coordinator.evidence.reject'), ['_id' => $evidence->id, 'reasonrejection' => '   '])
            ->assertSessionHas('error');

        $this->assertSame('PENDING', $evidence->fresh()->status);
    }

    public function test_the_student_sees_the_latest_rejection_reason(): void
    {
        $evidence = $this->evidence('PENDING');
        $this->actingAs($this->coordinator);
        $this->post(route('coordinator.evidence.reject'), ['_id' => $evidence->id, 'reasonrejection' => 'Falta el certificado.']);
        $this->post(route('coordinator.evidence.accept', ['id' => $evidence->id]));
        $this->post(route('coordinator.evidence.reject'), ['_id' => $evidence->id, 'reasonrejection' => 'Las horas no cuadran con el acta.']);

        $this->actingAs($this->student)
            ->get(route('evidence.view', ['id' => $evidence->id]))
            ->assertOk()
            ->assertSee('Rechazada: Las horas no cuadran con el acta.')
            ->assertSeeInOrder(['Motivos de rechazo anteriores', 'Falta el certificado.', 'Las horas no cuadran con el acta.']);
    }

    public function test_only_coordinators_see_the_evidence_lists(): void
    {
        $secretary = User::factory()->create();
        $secretary->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));

        $this->actingAs($this->coordinator)->get(route('coordinator.evidence.list.all'))->assertOk();
        $this->actingAs($secretary)->get(route('coordinator.evidence.list.all'))->assertNotFound();
    }

    public function test_users_without_a_coordinator_profile_cannot_review(): void
    {
        $evidence = $this->evidence('PENDING');
        $this->actingAs($this->student);

        $this->get(route('coordinator.evidence.view', ['id' => $evidence->id]))->assertNotFound();
        $this->post(route('coordinator.evidence.accept', ['id' => $evidence->id]))->assertNotFound();

        $this->assertSame('PENDING', $evidence->fresh()->status);
    }

    private function proofOf(Evidence $evidence): Proof
    {
        $file = File::create(['name' => 'certificado', 'type' => 'pdf', 'route' => 'proofs/certificado.pdf', 'size' => 3]);

        return Proof::create(['evidence_id' => $evidence->id, 'file_id' => $file->id]);
    }

    private function evidence(string $status): Evidence
    {
        return Evidence::create([
            'title' => 'Organización de mesas',
            'description' => 'Organicé las mesas del congreso durante dos horas.',
            'hours' => 2,
            'status' => $status,
            'user_id' => $this->student->id,
            'committee_id' => $this->committee->id,
        ]);
    }
}
