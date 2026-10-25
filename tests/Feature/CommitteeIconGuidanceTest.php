<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommitteeIconGuidanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_committee_icon_help_matches_the_bundled_font_awesome_version(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'PRESIDENT', 'slug' => 'president']));
        $icon = '<i class="far fa-compass"></i>';
        Committee::create(['name' => 'Navigation', 'icon' => $icon]);

        $this->actingAs($user)->get(route('president.committee.list'))
            ->assertOk()
            ->assertSee('Font Awesome 5.15.3')
            ->assertSee('https://fontawesome.com/v5/search?m=free', false)
            ->assertSee($icon)
            ->assertSee($icon, false);
    }
}
