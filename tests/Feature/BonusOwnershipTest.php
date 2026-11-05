<?php

namespace Tests\Feature;

use App\Models\Bonus;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonusOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['bonus_timestamp' => now()->addMonth()]);
        $this->role = Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']);
    }

    public function test_a_secretary_manages_their_committees_bonus(): void
    {
        $secretary = $this->secretary();
        $bonus = $this->bonusOf($secretary);
        $this->actingAs($secretary->user);

        $this->get(route('secretary.bonus.edit', ['id' => $bonus->id]))->assertOk();
        $this->post(route('secretary.bonus.save'), [
            '_id' => $bonus->id, 'reason' => 'Montaje del escenario', 'hours' => 4, 'users' => $bonus->users->modelKeys(),
        ])->assertRedirect(route('secretary.bonus.list'));
        $this->assertEquals(4, $bonus->fresh()->hours);

        $this->post(route('secretary.bonus.remove'), ['_id' => $bonus->id])->assertRedirect(route('secretary.bonus.list'));
        $this->assertModelMissing($bonus);
    }

    public function test_a_secretary_cannot_touch_another_committees_bonus(): void
    {
        $bonus = $this->bonusOf($this->secretary());
        $this->actingAs($this->secretary()->user);

        $this->get(route('secretary.bonus.edit', ['id' => $bonus->id]))->assertNotFound();
        $this->post(route('secretary.bonus.save'), [
            '_id' => $bonus->id, 'reason' => 'Bono manipulado', 'hours' => 99, 'users' => $bonus->users->modelKeys(),
        ])->assertNotFound();
        $this->post(route('secretary.bonus.remove'), ['_id' => $bonus->id])->assertNotFound();

        $this->assertEquals(3, $bonus->fresh()->hours);
    }

    private function secretary(): Secretary
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role);

        return Secretary::forceCreate([
            'user_id' => $user->id,
            'committee_id' => Committee::create(['name' => 'Committee '.$user->id])->id,
        ]);
    }

    private function bonusOf(Secretary $secretary): Bonus
    {
        $bonus = Bonus::create(['reason' => 'Montaje del escenario', 'hours' => 3]);
        $bonus->committee()->associate($secretary->committee)->save();
        $bonus->users()->attach(User::factory()->create());

        return $bonus;
    }
}
