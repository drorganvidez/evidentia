<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CommitteeManagementTest extends TestCase
{
    use RefreshDatabase;

    public static function managers(): array
    {
        return [['LECTURE', 'lecture'], ['PRESIDENT', 'president']];
    }

    #[DataProvider('managers')]
    public function test_managers_create_committees_with_unique_names(string $role, string $prefix): void
    {
        $this->signInAs($role);

        $this->post(route($prefix.'.committee.management.new'), ['name' => 'Logística', 'icon' => '<i class="fas fa-warehouse"></i>'])
            ->assertRedirect(route($prefix.'.committee.list'));
        $this->post(route($prefix.'.committee.management.new'), ['name' => 'Logística'])
            ->assertSessionHasErrors('name');

        // besides the committee of the presidency that the migrations create
        $this->assertSame(['Logística'], Committee::where('presidency', false)->pluck('name')->all());
    }

    #[DataProvider('managers')]
    public function test_managers_rename_committees(string $role, string $prefix): void
    {
        $this->signInAs($role);
        $logistics = Committee::create(['name' => 'Logística']);
        $program = Committee::create(['name' => 'Programa']);

        $this->post(route($prefix.'.committee.management.save'), [
            'name_'.$logistics->id => 'Logística y montaje', 'icon_'.$logistics->id => '<i class="fas fa-truck"></i>',
            'name_'.$program->id => 'Programa',
        ])->assertRedirect(route($prefix.'.committee.list'));

        $this->assertSame('Logística y montaje', $logistics->fresh()->name);
        $this->assertSame('<i class="fas fa-truck"></i>', $logistics->fresh()->icon);
        $this->assertSame('Programa', $program->fresh()->name);
    }

    public function test_two_committees_cannot_end_up_with_the_same_name(): void
    {
        $this->signInAs('LECTURE');
        $logistics = Committee::create(['name' => 'Logística']);
        $program = Committee::create(['name' => 'Programa']);

        $this->post(route('lecture.committee.management.save'), [
            'name_'.$logistics->id => 'Programa',
            'name_'.$program->id => 'Programa',
        ])->assertSessionHas('error');

        $this->assertSame(['Logística', 'Programa'], Committee::where('presidency', false)->orderBy('id')->pluck('name')->all());
    }

    private function signInAs(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        $this->actingAs($user);

        return $user;
    }
}
