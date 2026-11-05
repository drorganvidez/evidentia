<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MeetingMinutesOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate(['meetings_timestamp' => now()->addMonth()]);
        $this->role = Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']);
    }

    public function test_a_secretary_edits_their_own_minutes(): void
    {
        $secretary = $this->secretary();
        $minutes = $this->minutesOf($secretary);

        $this->actingAs($secretary->user)
            ->post(route('secretary.meeting.manage.minutes.save'), ['_id' => $minutes->id] + $this->form())
            ->assertRedirect(route('secretary.meeting.manage.minutes.list'));

        $this->assertSame('Reunión inicial de Logística', $minutes->meeting->fresh()->title);
    }

    public function test_a_secretary_cannot_overwrite_another_committees_meeting(): void
    {
        $foreign = $this->minutesOf($this->secretary());
        $intruder = $this->secretary();

        $this->actingAs($intruder->user)->post(route('secretary.meeting.manage.minutes.save'), [
            '_id' => $this->minutesOf($intruder)->id,
            'meeting_minutes_id' => $foreign->id,
            'meeting_id' => $foreign->meeting_id,
        ] + $this->form());

        $this->assertModelExists($foreign);
        $this->assertSame('Reunión del comité', $foreign->meeting->fresh()->title);
    }

    public function test_a_secretary_removes_their_own_minutes(): void
    {
        $secretary = $this->secretary();
        $minutes = $this->minutesOf($secretary);
        $meeting = $minutes->meeting;

        $this->actingAs($secretary->user)
            ->post(route('secretary.meeting.manage.minutes.remove'), ['_id' => $minutes->id])
            ->assertRedirect(route('secretary.meeting.manage.minutes.list'));

        $this->assertModelMissing($minutes);
        $this->assertModelMissing($meeting);
    }

    public function test_a_secretary_cannot_remove_another_committees_minutes_by_sending_their_own_id(): void
    {
        $foreign = $this->minutesOf($this->secretary());
        $intruder = $this->secretary();

        $this->actingAs($intruder->user)->post(route('secretary.meeting.manage.minutes.remove'), [
            '_id' => $this->minutesOf($intruder)->id,
            'meeting_minutes_id' => $foreign->id,
        ]);

        $this->assertModelExists($foreign);
        $this->assertModelExists($foreign->meeting);
    }

    private function secretary(): Secretary
    {
        $user = User::factory()->create();
        $user->roles()->attach($this->role);

        return Secretary::forceCreate([
            'user_id' => $user->id,
            'committee_id' => Committee::create(['name' => 'Committee '.$user->id])->id,
        ]);
    }

    private function minutesOf(Secretary $secretary): MeetingMinutes
    {
        $meeting = Meeting::forceCreate([
            'title' => 'Reunión del comité', 'datetime' => '2026-10-20 17:00', 'place' => 'Sala de juntas',
            'type' => '1', 'modality' => '1', 'hours' => 2, 'committee_id' => $secretary->committee_id,
        ]);
        $meeting->users()->attach($secretary->user_id);

        return MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $secretary->id]);
    }

    private function form(): array
    {
        return [
            'title' => 'Reunión inicial de Logística',
            'type' => 1,
            'modality' => 1,
            'hours' => 1,
            'place' => 'Aula A3.10',
            'date' => '2026-10-16',
            'time' => '16:30',
            'users' => [User::factory()->create()->id],
            'points_json' => '[]',
        ];
    }
}
