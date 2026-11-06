<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Nobody reviews their own work: the attempt goes back with the error, changes nothing and stays in the log.
 */
class SelfReviewInControllersTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;

    private Committee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addWeek(),
            'validate_evidences_timestamp' => now()->addWeeks(2),
            'attendee_timestamp' => now()->addWeek(),
        ]);
        $this->committee = Committee::create(['name' => 'Programa']);
        $this->coordinator = User::factory()->create(['username' => 'carnav']);
        $this->coordinator->roles()->attach(Role::forceCreate(['rol' => 'COORDINATOR', 'slug' => 'coordinator']));
        Coordinator::forceCreate(['user_id' => $this->coordinator->id, 'committee_id' => $this->committee->id]);
    }

    public function test_a_coordinator_cannot_accept_their_own_evidence(): void
    {
        $evidence = $this->ownEvidence();
        $before = $this->snapshot();

        $this->actingAs($this->coordinator)
            ->from(route('coordinator.evidence.view', ['id' => $evidence->id]))
            ->post(route('coordinator.evidence.accept', ['id' => $evidence->id]))
            ->assertRedirect(route('coordinator.evidence.view', ['id' => $evidence->id]))
            ->assertSessionHas('error', 'No puedes aceptar ni rechazar una evidencia tuya. El intento queda anotado.');

        $this->assertSame('PENDING', $evidence->fresh()->status);
        $this->assertSame($before, $this->snapshot());
        $this->assertBlocked($evidence, 'evidence.accepted');
    }

    public function test_a_coordinator_cannot_reject_their_own_evidence(): void
    {
        $evidence = $this->ownEvidence();
        $before = $this->snapshot();

        $this->actingAs($this->coordinator)
            ->from(route('coordinator.evidence.view', ['id' => $evidence->id]))
            ->post(route('coordinator.evidence.reject'), ['_id' => $evidence->id, 'reasonrejection' => 'Me la rechazo a mí mismo.'])
            ->assertRedirect(route('coordinator.evidence.view', ['id' => $evidence->id]))
            ->assertSessionHas('error', 'No puedes aceptar ni rechazar una evidencia tuya. El intento queda anotado.');

        $this->assertSame('PENDING', $evidence->fresh()->status);
        $this->assertSame($before, $this->snapshot());
        $this->assertBlocked($evidence, 'evidence.rejected');
    }

    public function test_the_review_of_someone_else_is_recorded_with_the_previous_status(): void
    {
        $student = User::factory()->create();
        $student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $evidence = Evidence::create([
            'title' => 'Organización de mesas', 'description' => 'Mesas del congreso.', 'hours' => 2,
            'status' => 'PENDING', 'user_id' => $student->id, 'committee_id' => $this->committee->id,
        ]);

        $this->actingAs($this->coordinator)
            ->post(route('coordinator.evidence.reject'), ['_id' => $evidence->id, 'reasonrejection' => 'Falta el certificado de asistencia.'])
            ->assertRedirect(route('coordinator.evidence.list.rejected'));

        $log = ActivityLog::sole();
        $this->assertSame('evidence.rejected', $log->action);
        $this->assertSame('COORDINATOR', $log->acting_role);
        $this->assertSame($student->id, $log->target_user_id);
        $this->assertSame($this->committee->id, $log->committee_id);
        $this->assertSame('PENDING', $log->details['before']);
        $this->assertSame('Falta el certificado de asistencia.', $log->details['reason']);
        $this->assertSame($evidence->reasonRejection->id, $log->details['reason_rejection_id']);
        $this->assertFalse($log->flagged);
    }

    private function ownEvidence(): Evidence
    {
        return Evidence::create([
            'title' => 'Mi propia evidencia', 'description' => 'Coordiné el comité.', 'hours' => 2,
            'status' => 'PENDING', 'user_id' => $this->coordinator->id, 'committee_id' => $this->committee->id,
        ]);
    }

    private function assertBlocked(Evidence $evidence, string $attempted): void
    {
        $log = ActivityLog::sole();
        $this->assertSame('self_review.blocked', $log->action);
        $this->assertSame('COORDINATOR', $log->acting_role);
        $this->assertSame($evidence->id, $log->subject_id);
        $this->assertSame($this->coordinator->id, $log->target_user_id);
        $this->assertSame(['attempted' => $attempted], $log->details);
        $this->assertSame(['self_review'], $log->flags);
        $this->assertTrue($log->flagged);
    }

    /** Every row of every table but the ones of the log (and their counters). */
    private function snapshot(): string
    {
        $log = ['activity_log', 'activity_log_targets'];
        $data = [];
        foreach (DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name") as $table) {
            if (! in_array($table->name, $log, true)) {
                $data[$table->name] = DB::table($table->name)
                    ->when($table->name === 'sqlite_sequence', fn ($query) => $query->whereNotIn('name', $log))
                    ->orderBy('rowid')->get()->all();
            }
        }

        return hash('sha256', serialize($data));
    }
}
