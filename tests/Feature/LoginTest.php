<?php

namespace Tests\Feature;

use App\Models\Configuration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_there_is_no_public_registration(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', ['name' => 'Intrusa', 'email' => 'x@us.es', 'password' => 'secret123', 'password_confirmation' => 'secret123'])
            ->assertNotFound();

        $this->assertSame(0, User::count());
    }

    public function test_passwords_are_recovered_through_the_app_own_form(): void
    {
        $this->get(route('password.custom_reset'))->assertOk();
    }

    public function test_logging_in_from_the_login_page_lands_on_the_dashboard(): void
    {
        Configuration::forceCreate([]);
        $user = User::factory()->create(['email' => 'ana@us.es', 'password' => Hash::make('secret123')]);
        $user->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));

        $this->get(route('login'))->assertOk();
        $this->post(route('login'), ['email' => 'ana@us.es', 'password' => 'secret123'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }
}
