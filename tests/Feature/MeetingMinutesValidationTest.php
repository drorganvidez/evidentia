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

class MeetingMinutesValidationTest extends TestCase
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

    public function test_new_minutes_reject_negative_durations(): void
    {
        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), ['hours' => -3] + $this->form())
            ->assertSessionHasErrors('hours');
        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), ['hours' => 1, 'minutes' => -30] + $this->form())
            ->assertSessionHasErrors('minutes');

        $this->assertSame(0, Meeting::count());
    }

    public function test_edited_minutes_reject_negative_durations(): void
    {
        $meeting = Meeting::forceCreate(['title' => 'Reunión', 'hours' => 2, 'committee_id' => $this->secretary->committee_id]);
        $minutes = MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $this->secretary->id]);

        $this->post(route('secretary.meeting.manage.minutes.save'), ['_id' => $minutes->id, 'hours' => -3] + $this->form())
            ->assertSessionHasErrors('hours');

        $this->assertEquals(2, $meeting->fresh()->hours);
    }

    private function form(): array
    {
        return [
            'title' => 'Reunión inicial de Logística',
            'type' => 1,
            'modality' => 1,
            'place' => 'Aula A3.10',
            'date' => '2026-10-16',
            'time' => '16:30',
            'users' => [User::factory()->create()->id],
            'points_json' => '[]',
        ];
    }
}
