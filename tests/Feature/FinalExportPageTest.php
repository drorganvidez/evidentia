<?php

namespace Tests\Feature;

use App\Models\Configuration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalExportPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_president_export_form_posts_to_a_route_the_president_can_use(): void
    {
        Configuration::forceCreate([]);
        $president = User::factory()->create();
        $president->roles()->attach(Role::forceCreate(['rol' => 'PRESIDENT', 'slug' => 'president']));

        $this->actingAs($president)
            ->get(route('president.export'))
            ->assertOk()
            ->assertSee('action="'.route('president.export.save').'"', false);
    }
}
