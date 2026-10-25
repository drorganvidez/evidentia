<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\DefaultList;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultListOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_secretary_cannot_use_another_secretarys_list(): void
    {
        $role = Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']);
        $owner = $this->createSecretary($role);
        $intruder = $this->createSecretary($role);
        $list = DefaultList::create(['name' => 'Owner list', 'secretary_id' => $owner->id]);
        $list->users()->attach(User::factory()->create());

        $this->actingAs($intruder->user);
        $before = $this->databaseSnapshot();

        $this->get(route('secretary.defaultlist.edit', $list->id))->assertNotFound();
        $this->post(route('secretary.defaultlist.save'), [
            '_id' => $list->id, 'name' => 'Hijacked list', 'users' => [],
        ])->assertNotFound();
        $this->post(route('secretary.defaultlist.remove'), ['_id' => $list->id])->assertNotFound();
        $this->get(route('secretary.meeting.defaultlist', $list->id))->assertNotFound();

        $this->assertSame($before, $this->databaseSnapshot());
    }

    public function test_list_members_are_only_returned_for_existing_lists(): void
    {
        $role = Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']);
        $secretary = $this->createSecretary($role);
        $list = DefaultList::create(['name' => 'Own list', 'secretary_id' => $secretary->id]);

        $this->actingAs($secretary->user)
            ->get(route('secretary.meeting.defaultlist', $list->id + 1))
            ->assertNotFound();
    }

    public function test_users_without_a_secretary_profile_cannot_load_list_members(): void
    {
        $role = Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']);
        $list = DefaultList::create(['name' => 'Owner list', 'secretary_id' => $this->createSecretary($role)->id]);
        $student = User::factory()->create();
        $student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));

        $this->actingAs($student)
            ->get(route('secretary.meeting.defaultlist', $list->id))
            ->assertNotFound();
    }

    private function createSecretary(Role $role): Secretary
    {
        $user = User::factory()->create();
        $user->roles()->attach($role);
        $committee = Committee::create(['name' => 'Committee '.$user->id]);

        return Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $committee->id]);
    }
}
