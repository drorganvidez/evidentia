<?php

namespace Tests\Feature;

use App\Models\Bonus;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonusScopeTest extends TestCase
{
    use RefreshDatabase;

    private User $secretary;

    private Committee $committee;

    private Committee $other;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['bonus_timestamp' => now()->addMonth()]);
        $this->committee = Committee::create(['name' => 'Logística']);
        $this->other = Committee::create(['name' => 'Programa']);
        $this->secretary = User::factory()->create(['surname' => 'Secretaria']);
        $this->secretary->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        Secretary::forceCreate(['user_id' => $this->secretary->id, 'committee_id' => $this->committee->id]);
        $this->actingAs($this->secretary);
    }

    public function test_the_form_offers_only_the_people_of_the_committee(): void
    {
        $member = $this->worker($this->committee, 'PENDING', 'Miembro');
        $outsider = $this->worker($this->other, 'ACCEPTED', 'Ajena');
        $drafter = $this->worker($this->committee, 'DRAFT', 'Borrador');
        $lecturer = $this->worker($this->committee, 'ACCEPTED', 'Profesora');
        $lecturer->roles()->attach(Role::forceCreate(['rol' => 'LECTURE', 'slug' => 'lecture']));

        $this->get(route('secretary.bonus.create'))->assertOk()
            ->assertSee('Miembro')->assertSee('Secretaria')
            ->assertDontSee('Ajena')->assertDontSee('Borrador')->assertDontSee('Profesora');
    }

    public function test_a_bonus_for_someone_outside_the_committee_is_refused(): void
    {
        $member = $this->worker($this->committee, 'ACCEPTED', 'Miembro');
        $outsider = $this->worker($this->other, 'ACCEPTED', 'Ajena');

        $this->post(route('secretary.bonus.new'), ['reason' => 'Montaje del stand', 'hours' => 2, 'users' => [$member->id, $outsider->id]])
            ->assertSessionHasErrors('users.1');
        $this->assertSame(0, Bonus::count());
    }

    public function test_who_was_in_the_bonus_may_stay_when_it_is_edited(): void
    {
        $former = User::factory()->create(['surname' => 'Antigua']);
        $bonus = Bonus::forceCreate(['reason' => 'Montaje del stand', 'hours' => 2, 'committee_id' => $this->committee->id]);
        $bonus->users()->attach($former);

        $this->get(route('secretary.bonus.edit', $bonus->id))->assertOk()->assertSee('Antigua');
        $this->post(route('secretary.bonus.save'), ['_id' => $bonus->id, 'reason' => 'Montaje del stand', 'hours' => 3, 'users' => [$former->id]])
            ->assertSessionHasNoErrors()->assertRedirect(route('secretary.bonus.list'));
        $this->assertEquals(3, $bonus->fresh()->hours);
    }

    private function worker(Committee $committee, string $status, string $surname): User
    {
        $user = User::factory()->create(['surname' => $surname]);
        Evidence::create(['title' => 'Stand', 'description' => 'Stand', 'hours' => 1, 'status' => $status,
            'user_id' => $user->id, 'committee_id' => $committee->id, 'last' => true]);

        return $user;
    }
}
