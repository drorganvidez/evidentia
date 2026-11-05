<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\Evidence;
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
