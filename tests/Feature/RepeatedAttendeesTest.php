<?php

namespace Tests\Feature;

use App\Models\Bonus;
use App\Models\Committee;
use App\Models\Meeting;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RepeatedAttendeesTest extends TestCase
{
    use RefreshDatabase;

    private Secretary $secretary;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        // Config lee siempre la fila con id 1
        DB::table('configuration')->insert(['id' => 1, 'bonus_timestamp' => now()->addMonth()]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $committee = Committee::create(['name' => 'Logística']);
        $this->secretary = Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $committee->id]);
        $this->actingAs($user);
    }

    public function test_creating_minutes_ignores_repeated_attendees(): void
    {
        [$ana, $pedro] = User::factory()->count(2)->create();

        // Así llegan cuando el formulario envía a los asistentes dos veces
        $users = [$ana->id, $pedro->id, $ana->id, $pedro->id, $this->secretary->user_id];
        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), $this->minutes($users))
            ->assertRedirect(route('secretary.meeting.manage.minutes.list'));

        $meeting = Meeting::sole();
        $this->assertAttendees($meeting, [$ana->id, $pedro->id, $this->secretary->user_id]);
        $this->assertCount(1, $ana->meetings);
        $this->assertCount(1, $this->secretary->user->meetings);
    }

    public function test_editing_minutes_ignores_repeated_attendees(): void
    {
        [$ana, $pedro] = User::factory()->count(2)->create();
        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), $this->minutes([$ana->id]));
        $meeting = Meeting::sole();

        $this->post(route('secretary.meeting.manage.minutes.save'), $this->minutes([$pedro->id, $pedro->id, $ana->id, $ana->id]) + [
            'meeting_id' => $meeting->id,
            'meeting_minutes_id' => $meeting->meetingMinutes->id,
        ])->assertRedirect(route('secretary.meeting.manage.minutes.list'));

        $this->assertAttendees($meeting, [$ana->id, $pedro->id, $this->secretary->user_id]);
    }

    public function test_bonuses_ignore_repeated_users(): void
    {
        [$ana, $pedro] = User::factory()->count(2)->create();

        $this->post(route('secretary.bonus.new'), ['reason' => 'Montaje del stand', 'hours' => 2, 'users' => [$ana->id, $ana->id]])
            ->assertRedirect(route('secretary.bonus.list'));
        $bonus = Bonus::sole();
        $this->assertSame([$ana->id], DB::table('bonus_user')->where('bonus_id', $bonus->id)->pluck('user_id')->all());

        $this->post(route('secretary.bonus.save'), ['_id' => $bonus->id, 'reason' => 'Montaje del stand', 'hours' => 2, 'users' => [$pedro->id, $ana->id, $pedro->id]])
            ->assertRedirect(route('secretary.bonus.list'));
        $this->assertEqualsCanonicalizing([$ana->id, $pedro->id], DB::table('bonus_user')->where('bonus_id', $bonus->id)->pluck('user_id')->all());
    }

    private function minutes(array $users): array
    {
        return [
            'title' => 'Reunión inicial de Logística',
            'type' => 1,
            'modality' => 1,
            'hours' => 1,
            'minutes' => 30,
            'place' => 'Aula A3.10',
            'date' => '2026-10-16',
            'time' => '16:30',
            'users' => $users,
            'points_json' => '[]',
        ];
    }

    private function assertAttendees(Meeting $meeting, array $expected): void
    {
        $this->assertEqualsCanonicalizing($expected, DB::table('meeting_user')->where('meeting_id', $meeting->id)->pluck('user_id')->all());
    }
}
