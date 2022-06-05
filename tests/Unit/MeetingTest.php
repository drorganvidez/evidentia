<?php

namespace Tests\Unit;

use App\Http\Services\MeetingService;
use App\Http\Services\Service;
use Tests\TestCase;

class MeetingTest extends TestCase
{

    private Service $meetingService;

    public function __construct()
    {
        parent::__construct();
        $this->meetingService = new MeetingService();
    }

    public function setUp() : void
    {
        parent::setUp();
        $this->init();
    }

    public function tearDown() : void
    {
        // write code that runs at the end of each test
    }

    public function testCreateMeetingSuccess()
    {

        $meeting_data = [
            'title' => 'Meeting1',
            'datetime' => '2022-01-04 02:20:09',
            'place' => 'Online - Discord',
            'type' => 1,
            'modality' => 1,
            'hours' => 4.5
        ];

        $meeting = $this->meetingService->create($meeting_data);

        $this->assertNotNull($meeting->title);

        $meeting->delete();

    }

    public function testCreateMeetingFail()
    {

        $meeting_data = [
            'title' => '',
            'datetime' => '2022-01-04 02:20:09',
            'place' => 'Online - Discord',
            'type' => 1,
            'modality' => 1,
            'hours' => 4.5
        ];

        $meeting = $this->meetingService->create($meeting_data);

        $this->assertNull($meeting);

    }

    public function testUpdateMeetingSuccess()
    {

        $meeting_data = [
            'title' => 'Meeting1',
            'datetime' => '2022-01-04 02:20:09',
            'place' => 'Online - Discord',
            'type' => 1,
            'modality' => 1,
            'hours' => 4.5
        ];

        $meeting = $this->meetingService->create($meeting_data);

        $new_data = [
            'title' => 'Meeting Modificado',
            'datetime' => '2022-01-04 02:20:09',
            'place' => 'Online - Discord',
            'type' => 1,
            'modality' => 1,
            'hours' => 4.5
        ];

        $updated_meeting = $this->meetingService->update($meeting->id, $new_data);

        $this->assertEquals("Meeting Modificado", $updated_meeting->title);

    }

    public function testUpdateMeetingFail()
    {

        $meeting_data = [
            'title' => 'Meeting1',
            'datetime' => '2022-01-04 02:20:09',
            'place' => 'Online - Discord',
            'type' => 1,
            'modality' => 1,
            'hours' => 4.5
        ];

        $meeting = $this->meetingService->create($meeting_data);

        $new_data = [
            'title' => '',
            'datetime' => '2022-01-04 02:20:09',
            'place' => 'Online - Discord',
            'type' => 1,
            'modality' => 1,
            'hours' => 4.5
        ];

        $updated_meeting = $this->meetingService->update($meeting->id, $new_data);

        $this->assertNull($updated_meeting);

    }


}
