<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Coordinator;
use App\Models\DefaultList;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $lecture;

    private Committee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lecture = User::factory()->create();
        $this->lecture->roles()->attach($this->role('LECTURE'));
        $this->committee = Committee::create(['name' => 'Logística']);
    }

    public function test_saving_a_secretary_keeps_their_minutes_requests_and_lists(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role('SECRETARY'));
        $secretary = Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $this->committee->id]);
        $meeting = Meeting::forceCreate(['title' => 'Reunión 1', 'committee_id' => $this->committee->id, 'hours' => 2]);
        $minutes = MeetingMinutes::forceCreate(['meeting_id' => $meeting->id, 'secretary_id' => $secretary->id]);
        $request = MeetingRequest::forceCreate(['title' => 'Convocatoria', 'committee_id' => $this->committee->id, 'secretary_id' => $secretary->id]);
        $list = DefaultList::forceCreate(['name' => 'Lista', 'secretary_id' => $secretary->id]);

        $this->actingAs($this->lecture)
            ->post(route('lecture.user.management.save'), $this->form($user, ['SECRETARY']))
            ->assertSessionHas('success');

        $this->assertModelExists($minutes);
        $this->assertSame($secretary->id, $user->fresh()->secretary->id);
        $this->assertSame($secretary->id, $request->fresh()->secretary_id);
        $this->assertSame($secretary->id, $list->fresh()->secretary_id);
    }

    public function test_moving_a_coordinator_to_another_committee_keeps_their_profile(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role('COORDINATOR'));
        $coordinator = Coordinator::forceCreate(['user_id' => $user->id, 'committee_id' => $this->committee->id]);
        $other = Committee::create(['name' => 'Programa']);

        $this->actingAs($this->lecture)
            ->post(route('lecture.user.management.save'), ['committee' => $other->id] + $this->form($user, ['COORDINATOR']))
            ->assertSessionHas('success');

        $this->assertSame($coordinator->id, $user->fresh()->coordinator->id);
        $this->assertSame($other->id, $coordinator->fresh()->committee_id);
    }

    public function test_removing_the_secretary_role_removes_the_secretary_profile(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role('SECRETARY'));
        Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $this->committee->id]);

        $this->actingAs($this->lecture)
            ->post(route('lecture.user.management.save'), $this->form($user, ['STUDENT']))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertNull($user->secretary);
        $this->assertTrue($user->hasRole('STUDENT'));
        $this->assertFalse($user->hasRole('SECRETARY'));
    }

    private function role(string $name): Role
    {
        return Role::where('rol', $name)->first() ?? Role::forceCreate(['rol' => $name, 'slug' => strtolower($name)]);
    }

    private function form(User $user, array $roles): array
    {
        return [
            'user_id' => $user->id,
            'pass' => 'on',
            'roles' => array_map(fn ($role) => $this->role($role)->id, $roles),
            'committee' => $this->committee->id,
            'username' => $user->username,
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
        ];
    }
}
