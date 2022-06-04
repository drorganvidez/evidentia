<?php

namespace Tests\Feature\Api\v1;

use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use \Illuminate\Testing\AssertableJsonString;
use Throwable;

class MeetingTest extends TestCase
{
    /**
     * Functional testing for the meetings of the REST API.
     *
     * @return void
     */

    public function setUp() : void
    {
        parent::setUp();
        // write code that runs at the start of each test
    }

    public function tearDown() : void
    {
        // write code that runs at the end of each test
    }

    /**
     * @throws Throwable
     */
    private function createMeeting(): AssertableJsonString
    {
        return $this->putJson('/api/21/v1/meeting', [
            'title' => 'Meeting1',
            'datetime' => '2022-01-04 02:20:09',
            'place' => 'Online - Discord',
            'type' => 1,
            'modality' => 1,
            'hours' => 4.5
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ])->decodeResponseJson();
    }

    public function testCreateMeetingSuccess()
    {
        $response = $this->putJson('/api/21/v1/meeting', [
            'title' => 'Meeting1',
            'datetime' => '2022-01-04 02:20:09',
            'place' => 'Online - Discord',
            'type' => 1,
            'modality' => 1,
            'hours' => 4.5
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'title' => 'Meeting1',
                'datetime' => '2022-01-04 02:20:09',
                'place' => 'Online - Discord',
                'type' => 1,
                'modality' => 1,
                'hours' => 4.5
            ]);
    }

    public function testCreateMeetingFail()
    {
        $response = $this->putJson('/api/21/v1/meeting', [
            'datetime' => '2022-01-04 02:20:09',
            'place' => 'Online - Discord',
            'type' => 1,
            'modality' => 1,
            'hours' => 4.5
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->whereType('error', 'array')
                    ->whereType('error.title', 'array')
            );
    }

    public function testGetAllMeetingsSuccess()
    {
        $response = $this->getJson('/api/21/v1/meeting', [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->where('current_page', 1)
                    ->whereType('data', 'array')
                    ->etc()
            );
    }

    public function testGetAllMeetingsFail()
    {
        $response = $this->getJson('/api/21/v1/meeting');

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->where('status', 'Authorization token not found')
            );
    }

    public function testGetMeetingSuccess()
    {
        $meeting = $this->createMeeting();

        $response = $this->getJson('/api/21/v1/meeting/' . $meeting['id'], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'title' => $meeting['title'],
                    'datetime' => $meeting['datetime'],
                    'place' => $meeting['place'],
                    'type' => ['', 'ORDINARY', 'EXTRAORDINARY'][$meeting['type']],
                    'modality' =>  ['', 'F2F', 'TELEMATIC', 'MIXED', 'OTHER'][$meeting['modality']],
                    'hours' => $meeting['hours']
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function testGetMeetingFail()
    {
        $this->createMeeting();

        $response = $this->getJson('/api/21/v1/meeting/-1', [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @throws Throwable
     */
    public function testUpdateMeetingSuccess()
    {
        $meeting = $this->createMeeting();
        $updatedMeeting = [
            'title' => 'MeetingUpdated',
            'datetime' => '2022-01-04 02:20:09',
            'place' => 'Online - Skype',
            'type' => 1,
            'modality' => 1,
            'hours' => 4.5
        ];
        $response = $this->postJson('/api/21/v1/meeting/' . $meeting['id'], $updatedMeeting, [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'title' => $updatedMeeting['title'],
                    'datetime' => $updatedMeeting['datetime'],
                    'place' => $updatedMeeting['place'],
                    'type' => $updatedMeeting['type'],
                    'modality' => $updatedMeeting['modality'],
                    'hours' => $updatedMeeting['hours']
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function testUpdateMeetingFail()
    {
        $meeting = $this->createMeeting();
        $updatedMeeting = [
            'title' => 'MeetingUpdated',
            'datetime' => '2022-01-04 02:20:09',
            'place' => 'Online - Skype',
            'type' => 1,
            'modality' => 1
        ];
        $response = $this->postJson('/api/21/v1/meeting/' . $meeting['id'], $updatedMeeting, [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->whereType('error', 'array')
                    ->whereType('error.hours', 'array')
            );
    }

    /**
     * @throws Throwable
     */
    public function testDeleteMeetingSuccess()
    {
        $meeting = $this->createMeeting();

        $response = $this->deleteJson('/api/21/v1/meeting/' . $meeting['id'], [], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200);
    }

    /**
     * @throws Throwable
     */
    public function testDeleteMeetingFail()
    {
        $meeting = $this->createMeeting();

        $response = $this->deleteJson('/api/21/v1/meeting/-45', [], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->whereType('error', 'array')
                    ->whereType('error.id', 'string')
            );
    }
}