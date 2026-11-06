<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Coordinator;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        }

        return $user->load('roles');
    }

    public function test_the_labels_of_the_roles(): void
    {
        $this->assertSame('Coordinador de registro', Roles::label('REGISTER_COORDINATOR'));
        $this->assertSame('Profesor', Roles::label('LECTURE'));
        $this->assertSame('—', Roles::label(null));
        $this->assertSame('OTHER', Roles::label('OTHER'));
    }

    public function test_the_role_line_of_a_person(): void
    {
        $this->assertSame('como coordinador', Roles::asLabel('COORDINATOR'));
        $this->assertSame('como secretario de Logística', Roles::asLabel('SECRETARY', 'Logística'));
        $this->assertSame('como coordinador de registro', Roles::asLabel('REGISTER_COORDINATOR'));
        $this->assertNull(Roles::asLabel(null, 'Logística'));
    }

    public function test_the_highest_role_follows_the_priority(): void
    {
        $this->assertSame('LECTURE', Roles::highest($this->userWith('STUDENT', 'PRESIDENT', 'LECTURE')));
        $this->assertSame('COORDINATOR', Roles::highest($this->userWith('STUDENT', 'SECRETARY', 'COORDINATOR')));
        $this->assertSame('STUDENT', Roles::highest($this->userWith('STUDENT')));
        $this->assertNull(Roles::highest($this->userWith()));
    }

    public function test_the_committees_of_a_coordinator_and_a_secretary(): void
    {
        $logistica = Committee::create(['name' => 'Logística']);
        $programa = Committee::create(['name' => 'Programa']);
        $both = $this->userWith('COORDINATOR', 'SECRETARY');
        Coordinator::forceCreate(['user_id' => $both->id, 'committee_id' => $logistica->id]);
        Secretary::forceCreate(['user_id' => $both->id, 'committee_id' => $programa->id]);
        $student = $this->userWith('STUDENT');
        // una fila de coordinador sin el rol no da comité
        Coordinator::forceCreate(['user_id' => $student->id, 'committee_id' => $logistica->id]);

        $this->assertSame([$logistica->id, $programa->id], Roles::committeeIds($both->fresh()));
        $this->assertSame([], Roles::committeeIds($student->fresh()));
    }

    public function test_the_managers_are_the_president_and_the_lecturers(): void
    {
        $this->assertTrue(Roles::isManager($this->userWith('PRESIDENT')));
        $this->assertTrue(Roles::isManager($this->userWith('LECTURE')));
        $this->assertFalse(Roles::isManager($this->userWith('COORDINATOR', 'STUDENT')));
    }
}
