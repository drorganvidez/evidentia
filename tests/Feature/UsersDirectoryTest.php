<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_secretaries_get_only_the_names_of_the_users(): void
    {
        $secretary = User::factory()->create();
        $secretary->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $student = User::factory()->create(['name' => 'Ana', 'surname' => 'García', 'email' => 'ana@us.es', 'biography' => 'Privada']);

        $this->actingAs($secretary)
            ->get(route('gp.users.all'))
            ->assertOk()
            ->assertJsonFragment(['id' => $student->id, 'name' => 'Ana', 'surname' => 'García'])
            ->assertDontSee('ana@us.es')
            ->assertDontSee('Privada');
    }

    public function test_students_cannot_list_the_users(): void
    {
        $student = User::factory()->create();
        $student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        User::factory()->create(['email' => 'profesora@us.es']);

        $this->actingAs($student)
            ->get(route('gp.users.all'))
            ->assertNotFound()
            ->assertDontSee('profesora@us.es');
    }
}
