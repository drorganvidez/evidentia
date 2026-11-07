<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NormalizeUsersTest extends TestCase
{
    use RefreshDatabase;

    private User $lecture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lecture = $this->user('LECTURE', ['username' => 'profe', 'email' => 'profe@us.es', 'name' => 'Ana']);
    }

    private function user(string $rol, array $data): User
    {
        $user = User::factory()->create($data);
        $user->roles()->attach(Role::where('rol', $rol)->first() ?? Role::forceCreate(['rol' => $rol, 'slug' => strtolower($rol)]));

        return $user;
    }

    public function test_the_lecturer_fixes_mails_names_and_access_of_everyone_but_the_lecturers(): void
    {
        $student = $this->user('STUDENT', ['username' => 'JuaPer', 'email' => 'juan@gmail.com', 'name' => 'Juan José', 'surname' => 'Pérez Núñez', 'block' => true]);
        $other = $this->user('LECTURE', ['username' => 'otro', 'email' => 'otro@us.es', 'name' => 'Luis']);

        $this->actingAs($this->lecture)->get(route('lecture.user.list'))->assertOk()->assertSee('Arreglar datos');

        $this->post(route('management.user.normalize'), ['fix_email' => '1', 'fix_uppercase' => '1', 'fix_access' => '1'])
            ->assertRedirect(route('lecture.user.list'))
            ->assertSessionHas('success', 'Datos arreglados en 1 usuario.');

        $student->refresh();
        $this->assertSame('juaper@alum.us.es', $student->email);
        $this->assertSame('JUAN JOSÉ', $student->name);
        $this->assertSame('PÉREZ NÚÑEZ', $student->surname);
        $this->assertFalse((bool) $student->block);
        $this->assertSame('otro@us.es', $other->fresh()->email);
        $this->assertSame('Ana', $this->lecture->fresh()->name);
        $this->assertDatabaseHas('activity_log', ['action' => 'user.edited']);
        $this->assertDatabaseHas('activity_log', ['action' => 'user.unblocked']);
    }

    public function test_a_mail_another_account_uses_is_kept(): void
    {
        $this->user('LECTURE', ['username' => 'profe2', 'email' => 'mari@alum.us.es']);
        $student = $this->user('STUDENT', ['username' => 'mari', 'email' => 'mari@gmail.com']);

        $this->actingAs($this->lecture)
            ->post(route('management.user.normalize'), ['fix_email' => '1'])
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'A 1 no se le cambió el correo'));

        $this->assertSame('mari@gmail.com', $student->fresh()->email);
    }

    public function test_it_needs_a_choice_and_only_the_lecturer_may(): void
    {
        $student = $this->user('STUDENT', ['username' => 'juan', 'email' => 'juan@gmail.com']);
        $this->actingAs($this->lecture)->post(route('management.user.normalize'))->assertSessionHas('error');

        $president = $this->user('PRESIDENT', ['username' => 'pres', 'email' => 'pres@gmail.com']);
        $this->actingAs($president)->post(route('management.user.normalize'), ['fix_email' => '1']);
        $this->actingAs($president)->get(route('president.user.list'))->assertOk()->assertDontSee('Arreglar datos');
        $this->assertSame('juan@gmail.com', $student->fresh()->email);
    }
}
