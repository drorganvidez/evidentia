<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Coordinator;
use App\Models\DefaultList;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $lecture;

    private Committee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lecture = User::factory()->create();
        $this->lecture->roles()->attach($this->role('LECTURE'));
        $this->committee = Committee::create(['name' => 'Logística']);
    }

    public function test_saving_a_secretary_keeps_their_minutes_requests_and_lists(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role('SECRETARY'));
        $secretary = Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $this->committee->id]);
        $meeting = Meeting::forceCreate(['title' => 'Reunión 1', 'committee_id' => $this->committee->id, 'hours' => 2]);
        $minutes = MeetingMinutes::forceCreate(['meeting_id' => $meeting->id, 'secretary_id' => $secretary->id]);
        $request = MeetingRequest::forceCreate(['title' => 'Convocatoria', 'committee_id' => $this->committee->id, 'secretary_id' => $secretary->id]);
        $list = DefaultList::forceCreate(['name' => 'Lista', 'secretary_id' => $secretary->id]);

        $this->actingAs($this->lecture)
            ->post(route('lecture.user.management.save'), $this->form($user, ['SECRETARY']))
            ->assertSessionHas('success');

        $this->assertModelExists($minutes);
        $this->assertSame($secretary->id, $user->fresh()->secretary->id);
        $this->assertSame($secretary->id, $request->fresh()->secretary_id);
        $this->assertSame($secretary->id, $list->fresh()->secretary_id);
    }

    public function test_moving_a_coordinator_to_another_committee_keeps_their_profile(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role('COORDINATOR'));
        $coordinator = Coordinator::forceCreate(['user_id' => $user->id, 'committee_id' => $this->committee->id]);
        $other = Committee::create(['name' => 'Programa']);

        $this->actingAs($this->lecture)
            ->post(route('lecture.user.management.save'), ['committee' => $other->id] + $this->form($user, ['COORDINATOR']))
            ->assertSessionHas('success');

        $this->assertSame($coordinator->id, $user->fresh()->coordinator->id);
        $this->assertSame($other->id, $coordinator->fresh()->committee_id);
    }

    public function test_removing_the_secretary_role_removes_the_secretary_profile(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role('SECRETARY'));
        Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $this->committee->id]);

        $this->actingAs($this->lecture)
            ->post(route('lecture.user.management.save'), $this->form($user, ['STUDENT']))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertNull($user->secretary);
        $this->assertTrue($user->hasRole('STUDENT'));
        $this->assertFalse($user->hasRole('SECRETARY'));
    }

    public function test_a_rejected_save_changes_nothing(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role('STUDENT'));

        $this->actingAs($this->lecture)
            ->from(route('lecture.user.management', ['id' => $user->id]))
            ->post(route('lecture.user.management.save'), ['email' => $this->lecture->email, 'pass' => null] + $this->form($user, ['COORDINATOR']))
            ->assertSessionHasErrors('email');

        $user->refresh();
        $this->assertTrue($user->hasRole('STUDENT'));
        $this->assertFalse($user->hasRole('COORDINATOR'));
        $this->assertNull($user->coordinator);
        $this->assertFalse((bool) $user->block);
    }

    public function test_a_student_is_saved_without_a_committee(): void
    {
        $user = User::factory()->create(['surname' => 'Pollo']);
        $user->roles()->attach($this->role('STUDENT'));
        $form = ['surname' => 'Polo'] + $this->form($user, ['STUDENT']);
        unset($form['committee']);

        $this->actingAs($this->lecture)
            ->post(route('lecture.user.management.save'), $form)
            ->assertSessionHas('success');

        $this->assertSame('Polo', $user->fresh()->surname);
    }

    public function test_a_secretary_needs_a_committee(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role('STUDENT'));

        $this->actingAs($this->lecture)
            ->from(route('lecture.user.management', ['id' => $user->id]))
            ->post(route('lecture.user.management.save'), ['committee' => 999] + $this->form($user, ['SECRETARY']))
            ->assertRedirect(route('lecture.user.management', ['id' => $user->id]))
            ->assertSessionHasErrors('committee');

        $this->assertFalse($user->fresh()->hasRole('SECRETARY'));
    }

    public function test_the_president_manages_students(): void
    {
        $president = User::factory()->create();
        $president->roles()->attach($this->role('PRESIDENT'));
        $student = User::factory()->create();
        $student->roles()->attach($this->role('STUDENT'));
        $this->actingAs($president);

        $this->get(route('president.user.management', ['id' => $student->id]))->assertOk();
        $this->post(route('president.user.management.save'), $this->form($student, ['SECRETARY']))
            ->assertSessionHas('success');

        $this->assertTrue($student->fresh()->hasRole('SECRETARY'));
    }

    public function test_the_president_cannot_grant_the_lecture_role(): void
    {
        $president = User::factory()->create();
        $president->roles()->attach($this->role('PRESIDENT'));

        $this->actingAs($president)->post(route('president.user.management.save'), $this->form($president, ['PRESIDENT', 'LECTURE']));

        $president->refresh();
        $this->assertTrue($president->hasRole('PRESIDENT'));
        $this->assertFalse($president->hasRole('LECTURE'));
    }

    public function test_the_president_cannot_manage_lecturers(): void
    {
        $president = User::factory()->create();
        $president->roles()->attach($this->role('PRESIDENT'));
        $password = $this->lecture->password;
        $this->actingAs($president);

        $this->get(route('president.user.management', ['id' => $this->lecture->id]))->assertNotFound();
        $this->post(route('president.user.management.save'), [
            'password' => 'hijacked', 'password_confirmation' => 'hijacked',
        ] + $this->form($this->lecture, ['LECTURE']))->assertNotFound();

        $this->assertSame($password, $this->lecture->fresh()->password);
    }

    public function test_the_lecturer_deletes_every_other_user(): void
    {
        User::factory()->count(3)->create();

        $this->actingAs($this->lecture)
            ->post(route('management.user.delete.all'))
            ->assertRedirect(route('lecture.user.list'));

        $this->assertSame([$this->lecture->id], User::pluck('id')->all());
    }

    public function test_lecturers_and_presidents_add_students_by_hand(): void
    {
        $president = User::factory()->create();
        $president->roles()->attach($this->role('PRESIDENT'));
        $this->role('STUDENT');

        foreach (['lecture' => $this->lecture, 'president' => $president] as $prefix => $manager) {
            $this->actingAs($manager)->post(route('management.user.new'), [
                'name' => 'Lucía', 'surname' => 'Pérez López', 'email' => $prefix.'@alum.us.es', 'username' => $prefix.'user',
            ])->assertRedirect(route($prefix.'.user.list'));

            $user = User::where('username', $prefix.'user')->sole();
            $this->assertTrue($user->hasRole('STUDENT'));
            $this->assertSame('PEREZLOPEZ', $user->clean_surname);
        }
    }

    private function role(string $name): Role
    {
        return Role::where('rol', $name)->first() ?? Role::forceCreate(['rol' => $name, 'slug' => strtolower($name)]);
    }

    private function form(User $user, array $roles): array
    {
        return [
            'user_id' => $user->id,
            'pass' => 'on',
            'roles' => array_map(fn ($role) => $this->role($role)->id, $roles),
            'committee' => $this->committee->id,
            'username' => $user->username,
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
        ];
    }
}
