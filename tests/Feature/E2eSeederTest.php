<?php

namespace Tests\Feature;

use App\Models\Evidence;
use App\Models\User;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The seed of the end-to-end tests: one person per role who can sign in, and data in every state. */
class E2eSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_person_of_the_seed_signs_in(): void
    {
        $this->seed(E2eSeeder::class);

        foreach (['estudiante', 'companero', 'coordinador', 'secretario', 'registro', 'presidente', 'profesor'] as $who) {
            $this->post('/login', ['email' => "$who@e2e.test", 'password' => E2eSeeder::PASSWORD])->assertRedirect();
            $this->assertAuthenticatedAs(User::where('username', $who)->sole());
            $this->post(route('logout'));
        }

        $this->assertSame(['ACCEPTED', 'DRAFT', 'PENDING', 'REJECTED'], Evidence::distinct()->orderBy('status')->pluck('status')->all());
        $this->assertTrue(User::where('username', 'coordinador')->sole()->hasRole('COORDINATOR'));
    }
}
