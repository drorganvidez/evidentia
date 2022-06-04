<?php

namespace Tests\Feature\Api\v1;

use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

class EvidentiaTest extends TestCase
{
    private function login($email = 'alumno1@alumno1.com', $password = 'alumno1')
    {
        return $this->postJson('/api/21/v1/auth/login', [
            'email' => $email,
            'password' => $password
        ])->decodeResponseJson()['token'];
    }


    public function createEvidence()
    {

        return $this->putJson('/api/21/v1/evidence', [
            'title' => 'Evidence Test',
            'description' => 'This is an evidence test',
            'hours' => 3.5,
            'user_id' => 1,
            'comittee_id' => 2,
            'points_to' => 3,
            'status' => 1,
            'stamp' => '423f234g5345h465g74j6467j',
            'rand' => 0
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ])->decodeResponseJson();
    }

    public function testCreateEvidenceSuccess()
    {
        $response = $this->putJson('/api/21/v1/evidence', [
            'title' => 'Evidence Test',
            'description' => 'This is an evidence test',
            'hours' => 3.5,
            'user_id' => 1,
            'comittee_id' => 2,
            'points_to' => 3,
            'status' => 1,
            'stamp' => '423f234g5345h465g74j6467j',
            'rand' => false
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'title' => 'Evidence Test',
                'description' => 'This is an evidence test',
                'hours' => 3.5,
                'user_id' => 1,
                'comittee_id' => 2,
                'points_to' => 3,
                'status' => 1,
                'stamp' => '423f234g5345h465g74j6467j',
                'rand' => false
            ]);
    }

    public function testCreateEvidenceFail()
    {
        $response = $this->putJson('/api/21/v1/evidence', [
            'description' => 'This is an evidence test',
            'hours' => 3.5,
            'user_id' => 1,
            'comittee_id' => 2,
            'points_to' => 3,
            'status' => 1,
            'stamp' => '423f234g5345h465g74j6467j',
            'rand' => false
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

    public function testCreateEvidenceNotLogged()
    {
        $response = $this->putJson('/api/21/v1/evidence', [
            'title' => 'Evidence Test',
            'description' => 'This is an evidence test',
            'hours' => 3.5,
            'user_id' => 1,
            'comittee_id' => 2,
            'points_to' => 3,
            'status' => 1,
            'stamp' => '423f234g5345h465g74j6467j',
            'rand' => false
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->where('status', 'Authorization token not found'));
    }

    public function testGetAllEvidencesSuccess()
    {
        $response = $this->getJson('/api/21/v1/evidence', [
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


    public function testGetAllEvidencesNotLogged()
    {
        $response = $this->getJson('/api/21/v1/evidence');

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->where('status', 'Authorization token not found')
            );
    }

    public function testGetEvidenceSuccess()
    {
        $evidence = $this->createEvidence();

        $response = $this->getJson('/api/21/v1/evidence/' . $evidence['id'], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'title' => $evidence['title'],
                    'description' =>  $evidence['description'],
                    'hours' =>  $evidence['hours'],
                    'user_id' =>  $evidence['user_id'],
                    'comittee_id' =>  $evidence['comittee_id'],
                    'points_to' =>  $evidence['points_to'],
                    'status' =>  array('','DRAFT', 'PENDING')[$evidence['status']],
                    'stamp' =>  $evidence['stamp'],
                    'rand' =>  $evidence['rand']
                ]
            );
    }
    public function testGetNotExistingEvidence()
    {

        $response = $this->getJson('/api/21/v1/evidence/-1', [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([]);
    }

    public function testUpdateEvidenceSuccess()
    {
        $evidence = $this->createEvidence();
        $updatedEvidence = [
            'title' => 'Evidence Update Test',
            'description' => 'This is an update test',
            'hours' => 3.5,
            'user_id' => 3,
            'comittee_id' => 3,
            'points_to' => 2,
            'status' => 2,
            'stamp' => '423f234g5345h465g74j6467j',
            'rand' => false
        ];
        $response = $this->postJson('/api/21/v1/evidence/' . $evidence['id'], $updatedEvidence, [
            'Authorization' => 'Bearer ' . $this->login()
        ]);
        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'title' => $updatedEvidence['title'],
                    'description' =>  $updatedEvidence['description'],
                    'hours' =>  $updatedEvidence['hours'],
                    'user_id' =>  $updatedEvidence['user_id'],
                    'comittee_id' =>  $updatedEvidence['comittee_id'],
                    'points_to' =>  $updatedEvidence['points_to'],
                    'status' =>  $updatedEvidence['status'],
                    'stamp' =>  $updatedEvidence['stamp'],
                    'rand' =>  $updatedEvidence['rand']
                ]
            );
    }

    public function testUpdateEvidenceWithMissingAttribute()
    {
        $evidence = $this->createEvidence();
        $updatedEvidence = [
            'title' => 'Evidence Update Test',
            'hours' => 3.5,
            'user_id' => 3,
            'comittee_id' => 3,
            'points_to' => 2,
            'status' => 2,
            'stamp' => '423f234g5345h465g74j6467j',
            'rand' => false
        ];
        $response = $this->postJson('/api/21/v1/evidence/' . $evidence['id'], $updatedEvidence, [
            'Authorization' => 'Bearer ' . $this->login()
        ]);
        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->whereType('error', 'array')
                    ->whereType('error.description', 'array')
            );
    }

    public function testDeleteEvidenceSuccess()
    {
        $evidence = $this->createEvidence();

        $response = $this->deleteJson('/api/21/v1/evidence/' . $evidence['id'], [], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);
        $response
            ->assertStatus(200);

        $response = $this->getJson('/api/21/v1/evidence/' . $evidence['id'], [
            'Authorization' => 'Bearer ' . $this->login()]);
        $response
            ->assertStatus(200)
            ->assertJson([]);
    }

    public function testDeleteNotExistingEvidence()
    {

        $response = $this->deleteJson('/api/21/v1/evidence/-23', [], [
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
