<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommitteeIconGuidanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_committees_choose_a_lucide_icon_and_the_old_markup_is_never_printed(): void
    {
        Configuration::forceCreate(['upload_evidences_timestamp' => now()->addWeek()]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'PRESIDENT', 'slug' => 'president']));
        $legacy = Committee::create(['name' => 'Navigation', 'icon' => '<i class="far fa-compass"></i>']);
        $lucide = Committee::create(['name' => 'Logística', 'icon' => 'warehouse']);

        $this->actingAs($user)->get(route('president.committee.list'))
            ->assertOk()
            ->assertDontSee('<i class="far fa-compass"></i>', false)
            ->assertSee('name="icon_'.$legacy->id.'" value="&lt;i class=&quot;far fa-compass&quot;&gt;&lt;/i&gt;"', false)
            ->assertSee('name="icon_'.$lucide->id.'" value="warehouse"', false)
            ->assertSee('name="icon" value="compass"', false);
    }

    public function test_a_committee_saved_from_its_dialog_keeps_the_others(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'PRESIDENT', 'slug' => 'president']));
        $edited = Committee::create(['name' => 'Logística', 'icon' => '<i class="fas fa-warehouse"></i>']);
        $other = Committee::create(['name' => 'Comunicación', 'icon' => 'megaphone']);

        $this->actingAs($user)->post(route('president.committee.management.save'), [
            'icon_'.$edited->id => 'warehouse', 'name_'.$edited->id => 'Logística y montaje',
        ])->assertRedirect(route('president.committee.list'));

        $this->assertSame(['Logística y montaje', 'warehouse'], [$edited->fresh()->name, $edited->fresh()->icon]);
        $this->assertSame(['Comunicación', 'megaphone'], [$other->fresh()->name, $other->fresh()->icon]);
    }
}
