<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Role;
use App\Models\User;

class UserListScreenTest extends ScreenTestCase
{
    public function test_the_lecturer_sees_everybody_but_themselves_with_the_mass_actions(): void
    {
        $this->screen('profesor', route('lecture.user.list'))
            ->assertSee('Alumnos')
            ->assertSee('Pérez Romero, Lucía')
            ->assertSee('Moreno Jiménez, Elena')
            ->assertSee('Coordinador de Logística')
            ->assertDontSee('Romero Organvídez, David')
            ->assertSee('action="'.route('management.user.delete.all').'"', false)
            ->assertSee('href="'.route('lecture.import').'"', false)
            ->assertSee('action="'.route('management.user.new').'"', false)
            ->assertSee('href="'.route('lecture.user.management', ['id' => $this->person('estudiante')->id]).'"', false);
    }

    public function test_the_president_neither_sees_lecturers_nor_deletes_everybody(): void
    {
        $this->screen('presidente', route('president.user.list'))
            ->assertSee('Pérez Romero, Lucía')
            ->assertDontSee('Romero Organvídez, David')
            ->assertDontSee(route('management.user.delete.all'))
            ->assertSee('href="'.route('president.user.management', ['id' => $this->person('estudiante')->id]).'"', false);
    }

    public function test_the_filters_and_the_order_live_in_the_url(): void
    {
        $this->person('companero')->forceFill(['block' => true])->save();

        $this->screen('profesor', route('lecture.user.list', ['rol' => 'COORDINATOR']))
            ->assertSee('Navarro Ruiz, Carlos')
            ->assertDontSee('Pérez Romero, Lucía');

        $this->screen('profesor', route('lecture.user.list', ['estado' => 'bloqueados']))
            ->assertSee('García López, Juan')
            ->assertSee('Bloqueado')
            ->assertDontSee('Pérez Romero, Lucía');

        $this->screen('profesor', route('lecture.user.list', ['sort' => 'surname', 'dir' => 'desc']))
            ->assertSeeInOrder(['Torres Gómez, Pablo', 'Pérez Romero, Lucía', 'García López, Juan']);

        $this->screen('profesor', route('lecture.user.list'))
            ->assertSeeInOrder(['García López, Juan', 'Pérez Romero, Lucía', 'Torres Gómez, Pablo']);
    }

    public function test_the_server_orders_the_names_without_case_and_the_roles_from_the_highest(): void
    {
        User::factory()->create(['name' => 'ana', 'surname' => 'de la Fuente', 'username' => 'anafue'])
            ->roles()->attach(Role::where('rol', 'STUDENT')->sole());

        $this->screen('profesor', route('lecture.user.list'))
            ->assertSeeInOrder(['Castro Vidal, Inés', 'de la Fuente, ana', 'García López, Juan']);

        // president, coordinators, register, secretary and students; the names on a tie
        $this->screen('profesor', route('lecture.user.list', ['sort' => 'roles', 'dir' => 'asc']))
            ->assertSeeInOrder([
                'Moreno Jiménez, Elena', 'Castro Vidal, Inés', 'Navarro Ruiz, Carlos', 'Torres Gómez, Pablo', 'Ruiz Díaz, Marta',
                'de la Fuente, ana', 'García López, Juan', 'Pérez Romero, Lucía',
            ]);
        $this->screen('profesor', route('lecture.user.list', ['sort' => 'roles', 'dir' => 'desc']))
            ->assertSeeInOrder(['Pérez Romero, Lucía', 'García López, Juan', 'Ruiz Díaz, Marta', 'Navarro Ruiz, Carlos', 'Moreno Jiménez, Elena']);
    }

    public function test_a_failed_new_user_reopens_its_dialog(): void
    {
        $this->actingAs($this->person('profesor'))
            ->from(route('lecture.user.list'))
            ->post(route('management.user.new'), ['name' => 'Ana'])
            ->assertRedirect(route('lecture.user.list'));

        $this->screen('profesor', route('lecture.user.list'))
            ->assertSee('data-open', false)
            ->assertSee('Revisa el formulario');
    }
}
