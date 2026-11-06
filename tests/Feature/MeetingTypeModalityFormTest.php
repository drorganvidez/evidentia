<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Diary;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MeetingTypeModalityFormTest extends TestCase
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

    public function test_editing_a_meeting_request_keeps_its_type_and_modality(): void
    {
        $request = $this->request();

        $html = $this->get(route('secretary.meeting.manage.request.edit', ['id' => $request->id]))->assertOk()->getContent();

        $this->assertSame(['2', '3'], [$this->selected($html, 'type'), $this->selected($html, 'modality')]);
    }

    public function test_editing_minutes_keeps_their_type_and_modality(): void
    {
        $meeting = Meeting::forceCreate([
            'title' => 'Reunión extraordinaria', 'type' => '2', 'modality' => '2', 'hours' => 1,
            'committee_id' => $this->secretary->committee_id,
        ]);
        $minutes = MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $this->secretary->id]);
        $edit = route('secretary.meeting.manage.minutes.edit', ['id' => $minutes->id]);

        $html = $this->get($edit)->assertOk()->getContent();
        $this->assertSame(['2', '2'], [$this->selected($html, 'type'), $this->selected($html, 'modality')]);

        // tras un error de validación se mantiene lo que había elegido el secretario
        $html = $this->from($edit)->followingRedirects()->post(route('secretary.meeting.manage.minutes.save'), [
            '_id' => $minutes->id, 'title' => 'R', 'type' => 1, 'modality' => 3,
        ])->assertOk()->getContent();
        $this->assertSame(['1', '3'], [$this->selected($html, 'type'), $this->selected($html, 'modality')]);
    }

    public function test_new_minutes_take_the_type_and_modality_of_their_request(): void
    {
        $request = $this->request();

        $html = $this->get(route('secretary.meeting.manage.minutes.create.step3', ['meeting_request' => $request->id]))
            ->assertOk()
            ->getContent();

        $this->assertSame(['2', '3'], [$this->selected($html, 'type'), $this->selected($html, 'modality')]);
    }

    private function request(): MeetingRequest
    {
        $request = MeetingRequest::forceCreate([
            'title' => 'Convocatoria extraordinaria', 'place' => 'Aula A3.10', 'datetime' => '2026-10-16 16:30',
            'type' => '2', 'modality' => '3',
            'committee_id' => $this->secretary->committee_id, 'secretary_id' => $this->secretary->id,
        ]);
        Diary::create(['meeting_request_id' => $request->id]);

        return $request;
    }

    /** The value a browser submits: the checked radio of the group. */
    private function selected(string $html, string $name): ?string
    {
        preg_match_all('/<input\b[^>]*\bname="'.$name.'"[^>]*>/', $html, $inputs);
        foreach ($inputs[0] as $input) {
            if (preg_match('/\schecked\b/', $input) && preg_match('/value="(\d)"/', $input, $value)) {
                return $value[1];
            }
        }

        return null;
    }
}
