<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\DefaultList;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultListMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_secretary_can_edit_an_existing_list(): void
    {
        $this->withoutExceptionHandling();
        $list = $this->createListForSecretary();

        $this->get(route('secretary.defaultlist.edit', $list->id))
            ->assertOk()
            ->assertViewIs('defaultlist.createandedit')
            ->assertViewHas('defaultlist', fn ($value) => $value->is($list))
            ->assertSee($list->name);
    }

    public function test_a_secretary_can_save_an_existing_list(): void
    {
        $this->withoutExceptionHandling();
        $list = $this->createListForSecretary();
        $previousMember = User::factory()->create();
        $newMember = User::factory()->create();
        $list->users()->attach($previousMember);

        $this->post(route('secretary.defaultlist.save'), [
            '_id' => $list->id,
            'name' => 'Updated list',
            'users' => [$newMember->id],
        ])->assertRedirect(route('secretary.defaultlist.list'))
            ->assertSessionHas('success', 'Lista editada con éxito.');

        $this->assertSame('Updated list', $list->fresh()->name);
        $this->assertSame([$newMember->id], $list->users()->pluck('users.id')->all());
    }

    public function test_a_secretary_can_remove_an_existing_list(): void
    {
        $this->withoutExceptionHandling();
        $list = $this->createListForSecretary();
        $list->users()->attach(User::factory()->create());
        $otherList = DefaultList::create(['name' => 'Keep this list']);

        $this->post(route('secretary.defaultlist.remove'), ['_id' => $list->id])
            ->assertRedirect(route('secretary.defaultlist.list'))
            ->assertSessionHas('success', 'Lista eliminada con éxito.');

        $this->assertModelMissing($list);
        $this->assertDatabaseMissing('default_list_user', ['default_list_id' => $list->id]);
        $this->assertModelExists($otherList);
    }

    public function test_missing_lists_return_not_found(): void
    {
        $list = $this->createListForSecretary();
        $missingId = $list->id + 1;

        $this->get(route('secretary.defaultlist.edit', $missingId))->assertNotFound();
        $this->post(route('secretary.defaultlist.save'), ['_id' => $missingId])->assertNotFound();
        $this->post(route('secretary.defaultlist.remove'), ['_id' => $missingId])->assertNotFound();

        $this->assertModelExists($list);
        $this->assertSame('Secretary list', $list->fresh()->name);
    }

    public function test_other_roles_cannot_access_the_list_actions(): void
    {
        $list = $this->createListForSecretary();
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $this->actingAs($user);

        $this->get(route('secretary.defaultlist.edit', $list->id))->assertNotFound();
        $this->post(route('secretary.defaultlist.save'), [
            '_id' => $list->id, 'name' => 'Unauthorized change',
        ])->assertNotFound();
        $this->post(route('secretary.defaultlist.remove'), ['_id' => $list->id])->assertNotFound();

        $this->assertModelExists($list);
        $this->assertSame('Secretary list', $list->fresh()->name);
    }

    public function test_guests_are_redirected_without_changing_lists(): void
    {
        $list = DefaultList::create(['name' => 'Guest-protected list']);

        $this->get(route('secretary.defaultlist.edit', $list->id))->assertRedirect(route('login'));
        $this->post(route('secretary.defaultlist.save'), [
            '_id' => $list->id, 'name' => 'Unauthorized change',
        ])->assertRedirect(route('login'));
        $this->post(route('secretary.defaultlist.remove'), ['_id' => $list->id])
            ->assertRedirect(route('login'));

        $this->assertModelExists($list);
        $this->assertSame('Guest-protected list', $list->fresh()->name);
    }

    public function test_the_existing_list_and_create_pages_still_work(): void
    {
        $this->createListForSecretary();

        $this->get(route('secretary.defaultlist.list'))->assertOk();
        $this->get(route('secretary.defaultlist.create'))->assertOk();
    }

    public function test_rejected_list_inputs_preserve_all_stored_data(): void
    {
        $list = $this->createListForSecretary();
        $list->users()->attach(User::factory()->create());
        $before = $this->databaseSnapshot();
        foreach ([0, -1, 'not-an-id', 999999] as $id) {
            $this->get('/secretary/defaultlist/edit/'.$id)->assertNotFound();
            $this->assertSame($before, $this->databaseSnapshot());
            foreach (['save', 'remove'] as $action) {
                $this->post('/secretary/defaultlist/'.$action, ['_id' => $id, 'name' => 'Rejected'])->assertNotFound();
                $this->assertSame($before, $this->databaseSnapshot());
            }
        }
        foreach (['', str_repeat('x', 256)] as $name) {
            $this->postJson('/secretary/defaultlist/save', ['_id' => $list->id, 'name' => $name])
                ->assertUnprocessable()->assertJsonValidationErrors('name');
            $this->assertSame($before, $this->databaseSnapshot());
        }
    }

    public function test_saved_members_flow_to_existing_list_consumers(): void
    {
        $list = $this->createListForSecretary();
        $member = User::factory()->create();
        $name = str_repeat('a', 255);
        $this->post('/secretary/defaultlist/save', ['_id' => $list->id, 'name' => $name, 'users' => [$member->id]])
            ->assertRedirect('/secretary/defaultlist/list');
        $this->get('/secretary/defaultlist/list')->assertOk()->assertSee($name);
        $this->get('/secretary/meeting/defaultlist/'.$list->id)
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', $member->id);
    }

    private function createListForSecretary(): DefaultList
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $committee = Committee::create(['name' => 'Test committee']);
        $secretary = Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $committee->id]);
        $this->actingAs($user);

        return DefaultList::create(['name' => 'Secretary list', 'secretary_id' => $secretary->id]);
    }
}
