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

class MeetingDeadlineTest extends TestCase
{
    use RefreshDatabase;

    private Secretary $secretary;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate(['meetings_timestamp' => now()->subDay()]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $this->secretary = Secretary::forceCreate([
            'user_id' => $user->id,
            'committee_id' => Committee::create(['name' => 'Logística'])->id,
        ]);
        $this->actingAs($user);
    }

    public function test_minutes_cannot_be_created_after_the_meetings_deadline(): void
    {
        $this->get(route('secretary.meeting.manage.minutes.create'))->assertNotFound();
        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), [
            'title' => 'Reunión inicial de Logística', 'type' => 1, 'modality' => 1, 'hours' => 3,
            'place' => 'Aula A3.10', 'date' => '2026-10-16', 'time' => '16:30',
            'users' => [User::factory()->create()->id], 'points_json' => '[]',
        ])->assertNotFound();

        $this->assertSame(0, Meeting::count());
    }

    public function test_minutes_cannot_be_edited_or_removed_after_the_meetings_deadline(): void
    {
        $meeting = Meeting::forceCreate(['title' => 'Reunión', 'hours' => 1, 'committee_id' => $this->secretary->committee_id]);
        $minutes = MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $this->secretary->id]);

        $this->get(route('secretary.meeting.manage.minutes.edit', ['id' => $minutes->id]))->assertNotFound();
        $this->post(route('secretary.meeting.manage.minutes.save'), ['_id' => $minutes->id, 'hours' => 40])->assertNotFound();
        $this->post(route('secretary.meeting.manage.minutes.remove'), ['_id' => $minutes->id])->assertNotFound();

        $this->assertEquals(1, $meeting->fresh()->hours);
        $this->get(route('secretary.meeting.manage.minutes.list'))
            ->assertOk()
            ->assertDontSee(route('secretary.meeting.manage.minutes.edit', ['id' => $minutes->id]));
    }
}
