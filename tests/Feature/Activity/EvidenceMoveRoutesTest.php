<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\User;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The three ways of moving an evidence to another committee, by role, state and deadline. */
class EvidenceMoveRoutesTest extends TestCase
{
    use RefreshDatabase;

    private Committee $logistics;

    private Committee $communication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(E2eSeeder::class);
        $this->logistics = Committee::where('name', 'Logística')->sole();
        $this->communication = Committee::where('name', 'Comunicación')->sole();
    }

    private function as(string $who): self
    {
        return $this->actingAs(User::where('username', $who)->sole());
    }

    private function evidence(string $title): Evidence
    {
        return Evidence::where('title', $title)->sole();
    }

    public function test_the_author_changes_the_committee_of_a_pending_evidence(): void
    {
        $evidence = $this->evidence('Montaje del photocall');

        $this->as('estudiante')->post(route('evidence.move'), ['_id' => $evidence->id, 'committee' => $this->communication->id])
            ->assertRedirect(route('activity.evidence', ['id' => $evidence->id]))
            ->assertSessionHas('success', 'Evidencia movida al comité Comunicación.');

        $this->assertSame($this->communication->id, (int) $evidence->fresh()->committee_id);
        $this->assertSame('PENDING', $evidence->fresh()->status);
        $this->assertSame(1, Evidence::where('title', 'Montaje del photocall')->count());
        $log = ActivityLog::where('action', 'evidence.moved')->sole();
        $this->assertSame('STUDENT', $log->acting_role);
        $this->assertSame('Logística', $log->details['before']['committee_name']);
        $this->assertSame('Comunicación', $log->details['after']['committee_name']);
        $this->assertSame($this->logistics->id, (int) $log->committee_id);
        $this->assertNull($log->target_user_id);
        $this->assertFalse($log->flagged);
    }

    public function test_the_author_moves_drafts_and_rejected_ones_but_not_accepted_ones(): void
    {
        foreach (['Borrador de la memoria', 'Recogida de material'] as $title) {
            $this->as('estudiante')->post(route('evidence.move'), ['_id' => $this->evidence($title)->id, 'committee' => $this->communication->id])
                ->assertRedirect();
            $this->assertSame($this->communication->id, (int) $this->evidence($title)->committee_id);
        }
        $this->assertNull(ActivityLog::where('action', 'evidence.moved')->where('subject_title', 'Borrador de la memoria')->sole()->committee_id);

        $this->as('estudiante')->post(route('evidence.move'), ['_id' => $this->evidence('Cartelería del vestíbulo')->id, 'committee' => $this->communication->id])
            ->assertNotFound();
        $this->as('companero')->post(route('evidence.move'), ['_id' => $this->evidence('Montaje del photocall')->id, 'committee' => $this->communication->id])
            ->assertNotFound();
    }

    public function test_the_same_committee_is_a_validation_error(): void
    {
        $evidence = $this->evidence('Montaje del photocall');

        $this->as('estudiante')->from(route('activity.evidence', ['id' => $evidence->id]))
            ->post(route('evidence.move'), ['_id' => $evidence->id, 'committee' => $this->logistics->id])
            ->assertSessionHasErrors(['committee' => 'Elige un comité distinto del actual.']);
        $this->as('estudiante')->post(route('evidence.move'), ['_id' => $evidence->id])->assertSessionHasErrors('committee');
        $this->assertSame(0, ActivityLog::where('action', 'evidence.moved')->count());
    }

    public function test_nobody_moves_after_their_deadline_but_the_management(): void
    {
        Configuration::query()->update(['upload_evidences_timestamp' => now()->subDay(), 'validate_evidences_timestamp' => now()->subDay()]);
        $evidence = $this->evidence('Atención en el registro');

        $this->as('companero')->post(route('evidence.move'), ['_id' => $evidence->id, 'committee' => $this->communication->id])->assertNotFound();
        $this->as('coordinador')->post(route('coordinator.evidence.move'), ['_id' => $evidence->id, 'committee' => $this->communication->id])->assertNotFound();

        $this->as('profesor')->post(route('management.evidence.move'), ['_id' => $evidence->id, 'committee' => $this->communication->id])
            ->assertRedirect(route('activity.evidence', ['id' => $evidence->id]))
            ->assertSessionHas('warning', 'Evidencia movida fuera de plazo. Queda señalada en el registro.');
        $log = ActivityLog::where('action', 'evidence.moved')->sole();
        $this->assertSame('LECTURE', $log->acting_role);
        $this->assertSame(['after_deadline'], $log->flags);
        $this->assertSame(User::where('username', 'companero')->sole()->id, (int) $log->target_user_id);
    }

    public function test_the_management_moves_an_accepted_one_within_the_deadline_without_signal(): void
    {
        $evidence = $this->evidence('Cartelería del vestíbulo');

        $this->as('presidente')->post(route('management.evidence.move'), ['_id' => $evidence->id, 'committee' => $this->communication->id])
            ->assertSessionHas('success', 'Evidencia movida al comité Comunicación.');
        $this->assertSame('ACCEPTED', $evidence->fresh()->status);
        $this->assertFalse(ActivityLog::where('action', 'evidence.moved')->sole()->flagged);

        $this->as('profesor')->post(route('management.evidence.move'), ['_id' => $this->evidence('Borrador de la memoria')->id, 'committee' => $this->communication->id])
            ->assertNotFound();
        $this->as('coordinador')->post(route('management.evidence.move'), ['_id' => $evidence->id, 'committee' => $this->logistics->id])
            ->assertNotFound();
    }

    public function test_the_coordinator_sends_a_pending_evidence_to_another_committee(): void
    {
        $evidence = $this->evidence('Atención en el registro');

        $this->as('coordinador')->post(route('coordinator.evidence.move'), ['_id' => $evidence->id, 'committee' => $this->communication->id])
            ->assertRedirect(route('coordinator.evidence.list.pending'))
            ->assertSessionHas('success', 'Evidencia enviada al comité Comunicación.');

        $this->assertSame($this->communication->id, (int) $evidence->fresh()->committee_id);
        $this->assertSame('COORDINATOR', ActivityLog::where('action', 'evidence.moved')->sole()->acting_role);

        // the accepted ones are not the coordinator's to move
        $this->as('coordinador')->post(route('coordinator.evidence.move'), ['_id' => $this->evidence('Cartelería del vestíbulo')->id, 'committee' => $this->communication->id])
            ->assertNotFound();
    }

    public function test_the_coordinator_never_moves_their_own_evidence(): void
    {
        $coordinator = User::where('username', 'coordinador')->sole();
        $own = Evidence::create([
            'title' => 'Inventario del almacén', 'description' => '<p>Inventario.</p>', 'hours' => 1, 'status' => 'PENDING',
            'user_id' => $coordinator->id, 'committee_id' => $this->logistics->id,
        ]);

        $this->actingAs($coordinator)->from(route('coordinator.evidence.list.pending'))
            ->post(route('coordinator.evidence.move'), ['_id' => $own->id, 'committee' => $this->communication->id])
            ->assertRedirect(route('coordinator.evidence.list.pending'))
            ->assertSessionHas('error', 'No puedes mover desde la revisión una evidencia tuya. El intento queda anotado.');

        $this->assertSame($this->logistics->id, (int) $own->fresh()->committee_id);
        $blocked = ActivityLog::where('action', 'self_review.blocked')->where('subject_id', $own->id)->sole();
        $this->assertSame(['self_review'], $blocked->flags);
        $this->assertSame('evidence.moved', $blocked->details['attempted']);
        $this->assertSame(0, ActivityLog::where('action', 'evidence.moved')->count());
    }
}
