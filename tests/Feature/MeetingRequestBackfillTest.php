<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Meeting;
use App\Models\MeetingRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The meeting requests held before the minutes linked them get their meeting when there is no doubt: one meeting of
 * the same committee on the same day.
 */
class MeetingRequestBackfillTest extends TestCase
{
    use RefreshDatabase;

    private Committee $logistica;

    private Committee $programa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logistica = Committee::create(['name' => 'Logística']);
        $this->programa = Committee::create(['name' => 'Programa']);
    }

    public function test_a_request_is_linked_to_the_only_meeting_of_its_committee_that_day(): void
    {
        $request = $this->request($this->logistica, '2026-10-16 16:30:00');
        $meeting = $this->meeting($this->logistica, '2026-10-16 17:00:00');
        $this->meeting($this->logistica, '2026-10-17 16:30:00');
        $this->meeting($this->programa, '2026-10-16 16:30:00');

        $this->migration()->up();

        $this->assertSame($meeting->id, (int) $request->fresh()->meeting_id);
    }

    public function test_doubtful_requests_are_left_alone(): void
    {
        $twoMeetings = $this->request($this->logistica, '2026-10-16 16:30:00');
        $this->meeting($this->logistica, '2026-10-16 10:00:00');
        $this->meeting($this->logistica, '2026-10-16 18:00:00');
        $noMeeting = $this->request($this->programa, '2026-10-20 16:30:00');
        $noCommittee = MeetingRequest::forceCreate(['title' => 'Sin comité', 'datetime' => '2026-10-16 16:30:00']);
        $noDate = MeetingRequest::forceCreate(['title' => 'Sin fecha', 'committee_id' => $this->logistica->id]);

        $this->migration()->up();

        foreach ([$twoMeetings, $noMeeting, $noCommittee, $noDate] as $request) {
            $this->assertNull($request->fresh()->meeting_id, $request->title);
        }
    }

    public function test_a_request_already_linked_keeps_its_meeting(): void
    {
        $linked = $this->meeting($this->logistica, '2026-10-10 16:30:00');
        $request = $this->request($this->logistica, '2026-10-16 16:30:00', $linked);
        $this->meeting($this->logistica, '2026-10-16 16:30:00');

        $this->migration()->up();

        $this->assertSame($linked->id, (int) $request->fresh()->meeting_id);
    }

    public function test_rolling_back_empties_the_column(): void
    {
        $request = $this->request($this->logistica, '2026-10-16 16:30:00');
        $this->meeting($this->logistica, '2026-10-16 16:30:00');
        $this->migration()->up();

        $this->migration()->down();

        $this->assertNull($request->fresh()->meeting_id);
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_11_06_000005_link_existing_meeting_requests_to_their_minutes.php');
    }

    private function request(Committee $committee, string $datetime, ?Meeting $meeting = null): MeetingRequest
    {
        return MeetingRequest::forceCreate([
            'title' => 'Convocatoria del '.$datetime, 'place' => 'Aula A3.10', 'datetime' => $datetime, 'type' => '1',
            'modality' => '1', 'committee_id' => $committee->id, 'meeting_id' => $meeting?->id,
        ]);
    }

    private function meeting(Committee $committee, string $datetime): Meeting
    {
        return Meeting::forceCreate([
            'title' => 'Reunión del '.$datetime, 'datetime' => $datetime, 'place' => 'Aula A3.10', 'type' => '1',
            'modality' => '1', 'hours' => 1, 'committee_id' => $committee->id,
        ]);
    }
}
