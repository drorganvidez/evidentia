<?php

namespace Tests\Feature\Activity;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Diary;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class SecretaryActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Secretary $secretary;

    protected function setUp(): void
    {
        parent::setUp();
        // the notices these actions send are checked in tests/Feature/Notifications
        Notification::fake();
        Storage::fake();
        Configuration::forceCreate(['meetings_timestamp' => now()->addMonth()]);
        $this->user = User::factory()->create();
        $this->user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $this->secretary = Secretary::forceCreate([
            'user_id' => $this->user->id,
            'committee_id' => Committee::create(['name' => 'Logística'])->id,
        ]);
        $this->actingAs($this->user);
    }

    public function test_a_request_with_its_sheet_records_both_in_the_same_request(): void
    {
        $this->post(route('secretary.meeting.manage.request.new'), $this->requestForm() + ['create_signature_sheet' => 'on'])
            ->assertRedirect(route('secretary.meeting.manage.request.list'));

        $request = MeetingRequest::sole();
        $sheet = SignatureSheet::sole();
        $logs = ActivityLog::orderBy('id')->get();
        $this->assertSame(['request.created', 'sheet.created'], $logs->pluck('action')->all());
        $this->assertSame(1, $logs->pluck('request_id')->unique()->count());
        $this->assertSame([$request->id, $sheet->id], $logs->pluck('subject_id')->all());
        $this->assertSame([$this->secretary->committee_id, $this->secretary->committee_id], $logs->pluck('committee_id')->all());
        $this->assertSame([
            'place' => 'Aula A3.10', 'datetime' => now()->addWeek()->format('Y-m-d').' 18:00', 'type' => '1', 'modality' => '2',
            'points' => ['Reparto de tareas', 'Turnos'],
        ], $logs[0]->details);
        $this->assertSame(['meeting_request_id' => $request->id], $logs[1]->details);
        $this->assertStringNotContainsString($sheet->random_identifier, json_encode($logs[1]->getAttributes()));
        Storage::assertExists('/meeting_requests/meeting_request_'.$request->id.'.pdf');
    }

    public function test_editing_a_request_records_only_what_changed(): void
    {
        $request = $this->request();

        $this->post(route('secretary.meeting.manage.request.save'), ['_id' => $request->id, 'place' => 'Aula A3.12'] + $this->requestForm());

        $log = ActivityLog::sole();
        $this->assertSame('request.edited', $log->action);
        $this->assertSame(['place' => 'Aula A3.10', 'modality' => '1', 'points' => ['Reparto de tareas']], $log->details['before']);
        $this->assertSame(['place' => 'Aula A3.12', 'modality' => '2', 'points' => ['Reparto de tareas', 'Turnos']], $log->details['after']);
    }

    public function test_removing_a_request_keeps_its_title_and_tells_the_sheet_was_unlinked(): void
    {
        $request = $this->request();
        SignatureSheet::create(['title' => 'Hoja', 'random_identifier' => '7391', 'meeting_request_id' => $request->id, 'secretary_id' => $this->secretary->id]);

        $this->post(route('secretary.meeting.manage.request.remove'), ['_id' => $request->id]);

        $log = ActivityLog::sole();
        $this->assertSame('request.deleted', $log->action);
        $this->assertSame('Reunión de logística', $log->subject_title);
        $this->assertSame(['sheet_unlinked' => true], $log->details);
        $this->assertSame($this->secretary->committee_id, $log->committee_id);
    }

    public function test_removing_a_sheet_counts_its_signatures(): void
    {
        $sheet = SignatureSheet::create(['title' => 'Hoja de firmas', 'random_identifier' => '7391', 'secretary_id' => $this->secretary->id]);
        $sheet->users()->attach(User::factory()->count(2)->create());

        $this->post(route('secretary.meeting.manage.signaturesheet.remove'), ['_id' => $sheet->id]);

        $log = ActivityLog::sole();
        $this->assertSame('sheet.deleted', $log->action);
        $this->assertSame('Hoja de firmas', $log->subject_title);
        $this->assertSame(['signatures_count' => 2], $log->details);
    }

    public function test_new_minutes_record_their_attendees_and_the_secretary_among_them(): void
    {
        $student = User::factory()->create();

        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), $this->minutesForm([$student->id]))
            ->assertRedirect(route('secretary.meeting.manage.minutes.list'));

        $minutes = MeetingMinutes::sole();
        $log = ActivityLog::sole();
        $this->assertSame('minutes.created', $log->action);
        $this->assertSame('SECRETARY', $log->acting_role);
        $this->assertSame($minutes->id, $log->subject_id);
        $this->assertSame('Reunión de montaje', $log->subject_title);
        $this->assertSame($this->secretary->committee_id, $log->committee_id);
        $this->assertEqualsCanonicalizing([$student->id, $this->user->id], $this->targets($log));
        $this->assertTrue($log->details['self_included']);
        $this->assertSame(2, $log->details['users']);
        $this->assertSame(1, $log->details['points']);
        $this->assertSame(2, $log->details['agreements']);
        $this->assertEquals(2.5, $log->details['hours']);
        $this->assertNull($log->details['meeting_request_id']);
        $this->assertFalse($log->flagged);
        Storage::assertExists('/meeting_minutes/meeting_minutes_'.$minutes->id.'.pdf');
    }

    public function test_new_minutes_link_their_own_meeting_request(): void
    {
        $request = $this->request();

        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), ['meeting_request' => $request->id] + $this->minutesForm([User::factory()->create()->id]));

        $meeting = Meeting::sole();
        $this->assertSame($meeting->id, (int) $request->fresh()->meeting_id);
        $this->assertSame($request->id, ActivityLog::sole()->details['meeting_request_id']);
    }

    public function test_new_minutes_never_link_the_request_of_another_secretary(): void
    {
        $other = Secretary::forceCreate(['user_id' => User::factory()->create()->id, 'committee_id' => $this->secretary->committee_id]);
        $request = MeetingRequest::forceCreate([
            'title' => 'Convocatoria ajena', 'datetime' => now()->format('Y-m-d').' 16:30:00',
            'committee_id' => $this->secretary->committee_id, 'secretary_id' => $other->id,
        ]);

        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), ['meeting_request' => $request->id] + $this->minutesForm([User::factory()->create()->id]));

        $this->assertSame(1, Meeting::count());
        $this->assertNull($request->fresh()->meeting_id);
        $this->assertNull(ActivityLog::sole()->details['meeting_request_id']);
    }

    public function test_editing_minutes_records_who_was_added_and_who_was_removed(): void
    {
        [$ana, $pedro, $lucia] = User::factory()->count(3)->create();
        $minutes = $this->minutes([$ana->id, $pedro->id, $this->user->id]);

        $this->post(route('secretary.meeting.manage.minutes.save'), ['_id' => $minutes->id, 'place' => 'Aula A3.12'] + $this->minutesForm([$ana->id, $lucia->id]));

        $log = ActivityLog::sole();
        $this->assertSame('minutes.edited', $log->action);
        $this->assertSame([$lucia->id], $log->details['users_added']);
        $this->assertSame([$pedro->id], $log->details['users_removed']);
        $this->assertSame(['place' => 'Aula A3.10'], array_intersect_key($log->details['before'], ['place' => 1]));
        $this->assertSame(['place' => 'Aula A3.12'], array_intersect_key($log->details['after'], ['place' => 1]));
        $this->assertEqualsCanonicalizing([$ana->id, $lucia->id, $this->user->id], $this->targets($log));
    }

    public function test_removing_minutes_keeps_the_title_and_the_attendees_who_lose_the_hours(): void
    {
        $student = User::factory()->create();
        $minutes = $this->minutes([$student->id, $this->user->id]);

        $this->post(route('secretary.meeting.manage.minutes.remove'), ['_id' => $minutes->id]);

        $log = ActivityLog::sole();
        $this->assertSame('minutes.deleted', $log->action);
        $this->assertSame('Reunión de logística', $log->subject_title);
        $this->assertSame($this->secretary->committee_id, $log->committee_id);
        $this->assertSame(['hours' => 2, 'users' => 2], $log->details);
        $this->assertEqualsCanonicalizing([$student->id, $this->user->id], $this->targets($log));
        Storage::assertMissing('/meeting_minutes/meeting_minutes_'.$minutes->id.'.pdf');
    }

    public function test_the_pdf_of_the_minutes_is_written_only_after_the_commit(): void
    {
        Activity::fake()->failOn(ActivityAction::MinutesCreated);
        $this->withoutExceptionHandling();

        try {
            $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), $this->minutesForm([User::factory()->create()->id]));
            $this->fail('The annotation should have failed');
        } catch (RuntimeException) {
        }

        $this->assertSame(0, Meeting::count());
        $this->assertSame([], Storage::allFiles('meeting_minutes'));
    }

    public function test_the_pdf_of_a_removed_request_is_deleted_only_after_the_commit(): void
    {
        $request = $this->request();
        Storage::put('/meeting_requests/meeting_request_'.$request->id.'.pdf', '%PDF-1.4');
        Activity::fake()->failOn(ActivityAction::RequestDeleted);
        $this->withoutExceptionHandling();

        try {
            $this->post(route('secretary.meeting.manage.request.remove'), ['_id' => $request->id]);
            $this->fail('The annotation should have failed');
        } catch (RuntimeException) {
        }

        $this->assertNotNull($request->fresh());
        Storage::assertExists('/meeting_requests/meeting_request_'.$request->id.'.pdf');
    }

    private function request(): MeetingRequest
    {
        $request = MeetingRequest::forceCreate([
            'title' => 'Reunión de logística', 'place' => 'Aula A3.10', 'datetime' => now()->addWeek()->format('Y-m-d').' 18:00:00',
            'type' => '1', 'modality' => '1', 'committee_id' => $this->secretary->committee_id, 'secretary_id' => $this->secretary->id,
        ]);
        Diary::create(['meeting_request_id' => $request->id])->diaryPoints()->create(['point' => 'Reparto de tareas']);

        return $request;
    }

    private function minutes(array $users): MeetingMinutes
    {
        $meeting = Meeting::forceCreate([
            'title' => 'Reunión de logística', 'datetime' => now()->format('Y-m-d').' 16:30:00', 'place' => 'Aula A3.10',
            'type' => '1', 'modality' => '1', 'hours' => 2, 'committee_id' => $this->secretary->committee_id,
        ]);
        $meeting->users()->attach($users);
        $minutes = MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $this->secretary->id]);
        Storage::put('/meeting_minutes/meeting_minutes_'.$minutes->id.'.pdf', '%PDF-1.4');

        return $minutes;
    }

    private function requestForm(): array
    {
        return [
            'title' => 'Reunión de logística', 'place' => 'Aula A3.10', 'date' => now()->addWeek()->format('Y-m-d'), 'time' => '18:00',
            'type' => 1, 'modality' => 2, 'points_list' => json_encode(['Reparto de tareas', 'Turnos']),
        ];
    }

    private function minutesForm(array $users): array
    {
        return [
            'title' => 'Reunión de montaje', 'type' => 1, 'modality' => 1, 'hours' => 2, 'minutes' => 30,
            'place' => 'Aula A3.10', 'date' => now()->format('Y-m-d'), 'time' => '16:30', 'users' => $users,
            'points_json' => json_encode([['title' => 'Reparto', 'duration' => 10, 'description' => 'Equipos', 'agreements' => [
                ['description' => 'Cada equipo nombra un responsable.'], ['description' => 'Turnos de mañana y tarde.'],
            ]]]),
        ];
    }

    /** @return list<int> */
    private function targets(ActivityLog $log): array
    {
        return DB::table('activity_log_targets')->where('activity_log_id', $log->id)->pluck('user_id')->map(fn ($id) => (int) $id)->all();
    }
}
