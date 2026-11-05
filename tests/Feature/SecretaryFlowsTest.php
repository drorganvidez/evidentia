<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\DefaultList;
use App\Models\Diary;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecretaryFlowsTest extends TestCase
{
    use RefreshDatabase;

    private Secretary $secretary;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate(['meetings_timestamp' => now()->addMonth()]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $this->secretary = Secretary::forceCreate([
            'user_id' => $user->id,
            'committee_id' => Committee::create(['name' => 'Logística'])->id,
        ]);
        $this->actingAs($user);
    }

    public function test_a_secretary_creates_a_default_list(): void
    {
        [$ana, $pedro] = User::factory()->count(2)->create();

        $this->post(route('secretary.defaultlist.new'), ['name' => 'Equipo de montaje', 'users' => [$ana->id, $pedro->id]])
            ->assertRedirect(route('secretary.defaultlist.list'));

        $list = DefaultList::sole();
        $this->assertSame($this->secretary->id, $list->secretary_id);
        $this->assertEqualsCanonicalizing([$ana->id, $pedro->id], $list->users->modelKeys());
    }

    public function test_a_default_list_only_takes_existing_users(): void
    {
        $this->post(route('secretary.defaultlist.new'), ['name' => 'Equipo de montaje', 'users' => [999]])
            ->assertSessionHasErrors('users.0');
        $this->assertSame(0, DefaultList::count());

        $list = DefaultList::create(['name' => 'Equipo', 'secretary_id' => $this->secretary->id]);
        $this->post(route('secretary.defaultlist.save'), ['_id' => $list->id, 'name' => 'Equipo', 'users' => [999]])
            ->assertSessionHasErrors('users.0');
    }

    public function test_new_minutes_start_from_one_of_the_secretarys_requests(): void
    {
        $request = $this->request();

        $this->post(route('secretary.meeting.manage.minutes.create.step1_p'), ['meeting_request' => $request->id])
            ->assertRedirect(route('secretary.meeting.manage.minutes.create.step2', ['meeting_request' => $request->id]));
        $this->post(route('secretary.meeting.manage.minutes.create.step1_p'), ['meeting_request' => ''])
            ->assertRedirect(route('secretary.meeting.manage.minutes.create.step2'));
    }

    public function test_a_secretary_edits_a_meeting_request_and_its_agenda(): void
    {
        $request = $this->request();

        $this->post(route('secretary.meeting.manage.request.save'), [
            '_id' => $request->id, 'title' => 'Reunión de logística corregida', 'place' => 'Aula A3.12',
            'date' => now()->addWeeks(2)->format('Y-m-d'), 'time' => '18:00', 'type' => 2, 'modality' => 3,
            'points_list' => json_encode(['Presupuesto', 'Turnos']),
        ])->assertRedirect(route('secretary.meeting.manage.request.list'));

        $request->refresh();
        $this->assertSame(['Reunión de logística corregida', 'Aula A3.12', '2', '3'], [$request->title, $request->place, (string) $request->type, (string) $request->modality]);
        $this->assertSame(['Presupuesto', 'Turnos'], $request->diary->diaryPoints->pluck('point')->all());
        Storage::assertExists('/meeting_requests/meeting_request_'.$request->id.'.pdf');
    }

    private function request(): MeetingRequest
    {
        $request = MeetingRequest::forceCreate([
            'title' => 'Reunión de logística', 'place' => 'Aula A3.10', 'datetime' => now()->addWeek(),
            'type' => '1', 'modality' => '1',
            'committee_id' => $this->secretary->committee_id, 'secretary_id' => $this->secretary->id,
        ]);
        Diary::create(['meeting_request_id' => $request->id])->diaryPoints()->create(['point' => 'Reparto de tareas']);

        return $request;
    }
}
