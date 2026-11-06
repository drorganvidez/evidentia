<?php

namespace Tests\Feature;

use App\Models\Configuration;
use App\Models\User;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** A course whose configuration row is missing (a database restored by hand) still opens its pages. */
class MissingConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_pages_open_without_the_configuration_row(): void
    {
        $this->seed(E2eSeeder::class);
        Configuration::query()->delete();

        foreach (['estudiante' => ['home', 'evidence.list', 'meeting.list', 'attendee.list'], 'profesor' => ['home', 'lecture.evidence.list', 'lecture.committee.list']] as $who => $routes) {
            $this->actingAs(User::where('username', $who)->sole());
            foreach ($routes as $route) {
                $this->get(route($route))->assertOk();
            }
        }
    }
}
