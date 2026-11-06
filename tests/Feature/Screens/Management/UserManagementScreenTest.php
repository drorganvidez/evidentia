<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Role;

class UserManagementScreenTest extends ScreenTestCase
{
    public function test_the_lecturer_manages_a_coordinator_with_their_committee(): void
    {
        $coordinator = $this->person('coordinador');
        $role = Role::where('rol', 'COORDINATOR')->sole();

        $this->screen('profesor', route('lecture.user.management', ['id' => $coordinator->id]))
            ->assertSee('Navarro Ruiz, Carlos')
            ->assertSee('action="'.route('lecture.user.management.save').'"', false)
            ->assertSee('name="user_id" value="'.$coordinator->id.'"', false)
            ->assertSee('value="'.$role->id.'"', false)
            ->assertSee('name="committee"', false)
            ->assertSee('name="pass"', false)
            ->assertSee('Profesor')
            ->assertSee('Horas computadas');
    }

    public function test_the_committee_starts_hidden_for_a_student(): void
    {
        $html = $this->screen('presidente', route('president.user.management', ['id' => $this->person('estudiante')->id]))
            ->assertSee('action="'.route('president.user.management.save').'"', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/x-show="hasAny\([^"]+\)"\s+x-cloak/', $html);
        // The president never grants the lecture role
        $this->assertDoesNotMatchRegularExpression('/name="roles\[\]"[^>]*value="'.Role::where('rol', 'LECTURE')->sole()->id.'"/', $html);
        $this->assertMatchesRegularExpression('/name="roles\[\]"[^>]*value="'.Role::where('rol', 'STUDENT')->sole()->id.'"/', $html);
    }

    public function test_a_failed_save_keeps_what_was_typed(): void
    {
        $student = $this->person('estudiante');
        $secretary = Role::where('rol', 'SECRETARY')->sole();

        $this->actingAs($this->person('profesor'))
            ->from(route('lecture.user.management', ['id' => $student->id]))
            ->post(route('lecture.user.management.save'), [
                'user_id' => $student->id, 'name' => 'Lucía', 'surname' => 'Pérez Romero', 'username' => 'estudiante',
                'email' => 'estudiante@e2e.test', 'roles' => [$secretary->id],
            ])
            ->assertSessionHasErrors('committee');

        $html = $this->screen('profesor', route('lecture.user.management', ['id' => $student->id]))
            ->assertSee('Comité no válido.')
            ->getContent();
        $this->assertDoesNotMatchRegularExpression('/x-show="hasAny\([^"]+\)"\s+x-cloak/', $html);
    }
}
