<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeMeetingDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_lists_the_meeting_requests_and_minutes_of_each_committee(): void
    {
        DB::table('configuration')->insert(['meetings_timestamp' => now()->addMonth()]);
        $committee = Committee::create(['name' => 'Logística']);
        $secretary = Secretary::forceCreate(['user_id' => User::factory()->create()->id, 'committee_id' => $committee->id]);
        MeetingRequest::forceCreate([
            'title' => 'Convocatoria de logística',
            'datetime' => '2026-10-20 17:00',
            'committee_id' => $committee->id,
            'secretary_id' => $secretary->id,
        ]);
        $meeting = Meeting::forceCreate(['title' => 'Reunión de logística', 'datetime' => '2026-10-20 17:00', 'committee_id' => $committee->id]);
        MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $secretary->id]);

        $this->assertCount(1, $committee->get_all_meeting_requests());
        $this->assertCount(1, $committee->get_all_meeting_minutes());

        $this->actingAs(User::factory()->create())->get('/')
            ->assertOk()
            ->assertSee('Convocatoria de logística')
            ->assertSee('Reunión de logística')
            ->assertSee(route('download.request', $committee->get_all_meeting_requests()->first()->id))
            ->assertSee(route('download.minutes', $meeting->meetingMinutes->id));
    }

    public function test_meeting_documents_can_be_downloaded_by_any_authenticated_user(): void
    {
        Storage::fake();
        $committee = Committee::create(['name' => 'Logística']);
        $secretary = Secretary::forceCreate(['user_id' => User::factory()->create()->id, 'committee_id' => $committee->id]);
        $request = MeetingRequest::forceCreate(['title' => 'Convocatoria', 'committee_id' => $committee->id, 'secretary_id' => $secretary->id]);
        Storage::put('/meeting_requests/meeting_request_'.$request->id.'.pdf', '%PDF-1.4');

        $this->get(route('download.request', $request->id))->assertRedirect(route('login'));
        ob_start(); // DownloadController cierra el búfer de salida activo
        $this->actingAs(User::factory()->create())->get(route('download.request', $request->id))->assertOk();
        $this->get(route('download.request', $request->id + 1))->assertNotFound();
    }
}
