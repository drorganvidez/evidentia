<?php

namespace Tests\Feature;

use App\Models\Bonus;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Meeting;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MeetingInputValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate(['meetings_timestamp' => now()->addMonth()]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => Committee::create(['name' => 'Logística'])->id]);
        $this->actingAs($user);
    }

    public function test_a_meeting_request_needs_a_valid_time(): void
    {
        $this->post(route('secretary.meeting.manage.request.new'), ['time' => 'mañana'] + $this->request())
            ->assertSessionHasErrors('time');

        $this->assertSame(0, MeetingRequest::count());
    }

    public function test_minutes_need_a_valid_time(): void
    {
        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), [
            'title' => 'Reunión inicial de Logística', 'type' => 1, 'modality' => 1, 'hours' => 1,
            'place' => 'Aula A3.10', 'date' => '2026-10-16', 'time' => 'mañana',
            'users' => [User::factory()->create()->id], 'points_json' => '[]',
        ])->assertSessionHasErrors('time');

        $this->assertSame(0, Meeting::count());
    }

    public function test_a_meeting_request_needs_a_valid_agenda(): void
    {
        foreach (['Reparto de tareas', '{"title": 5}', '[["Reparto"]]'] as $agenda) {
            $this->post(route('secretary.meeting.manage.request.new'), ['points_list' => $agenda] + $this->request())
                ->assertSessionHasErrors('points_list');
        }

        $this->assertSame(0, MeetingRequest::count());
    }

    public function test_attendees_and_bonus_users_must_exist(): void
    {
        Configuration::find(1)->update(['bonus_timestamp' => now()->addMonth()]);

        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), [
            'title' => 'Reunión inicial de Logística', 'type' => 1, 'modality' => 1, 'hours' => 1,
            'place' => 'Aula A3.10', 'date' => '2026-10-16', 'time' => '16:30',
            'users' => [999], 'points_json' => '[]',
        ])->assertSessionHasErrors('users.0');
        $this->post(route('secretary.bonus.new'), ['reason' => 'Montaje del escenario', 'hours' => 2, 'users' => [999]])
            ->assertSessionHasErrors('users.0');

        $this->assertSame(0, Meeting::count());
        $this->assertSame(0, Bonus::count());
    }

    private function request(): array
    {
        return [
            'title' => 'Reunión de logística', 'place' => 'Aula A3.10', 'date' => now()->addWeek()->format('Y-m-d'),
            'time' => '17:00', 'type' => 1, 'modality' => 1, 'points_list' => json_encode(['Reparto de tareas']),
        ];
    }
}
