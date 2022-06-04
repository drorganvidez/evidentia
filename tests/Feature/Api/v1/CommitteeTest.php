<?php

namespace Tests\Feature\Api\v1;

use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

class CommitteeTest extends TestCase
{
    /**
     * Functional testing for the committees of the REST API.
     *
     * @return void
     */

    public function setUp() : void {
        parent::setUp();
    }

    public function createCommittee()
    {
        return $this->putJson('/api/21/v1/commitee', [
            'icon' => '<i class="fas fa-piggy-bank"></i>',
            'name' => 'Sostenibilidad',
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ])->decodeResponseJson();
    }

    public function testCreateCommitteeSuccess()
    {
        $response = $this->putJson('/api/21/v1/commitee', [
            'icon' => '<i class="fas fa-piggy-bank"></i>',
            'name' => 'Sostenibilidad',
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'icon' => '<i class="fas fa-piggy-bank"></i>',
                'name' => 'Sostenibilidad',
            ]);
    }

    public function testCreateCommitteeFail()
    {
        $response = $this->putJson('/api/21/v1/commitee', [
            'icon' => '<i class="fas fa-piggy-bank"></i>',
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->whereType('error', 'array')
                    ->whereType('error.name', 'array')
            );
    }

    public function testGetAllCommitteesSuccess()
    {
        $response = $this->getJson('/api/21/v1/commitee', [
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

    public function testGetAllCommitteesFail()
    {
        $response = $this->getJson('/api/21/v1/commitee');

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->where('status', 'Authorization token not found')
            );
    }

    public function testGetCommitteeSuccess()
    {
        $committee = $this->createCommittee();

        $response = $this->getJson('/api/21/v1/commitee/' . $committee['id'], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'icon' => $committee['icon'],
                    'name' => $committee['name'],
                ]
            );
    }


    public function testGetCommitteeFail()
    {
        $response = $this->getJson('/api/21/v1/commitee/-1', [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([]);
    }


    public function testUpdateCommitteeSuccess()
    {
        $committee = $this->createCommittee();
        $updatedCommittee = [
            'icon' => '<i class="fas fa-piggy-bank"></i>',
            'name' => 'Sostenibilidad Actualizado',
        ];
        $response = $this->postJson('/api/21/v1/commitee/' . $committee['id'], $updatedCommittee, [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'icon' => $updatedCommittee['icon'],
                    'name' => $updatedCommittee['name'],
                ]
            );
    }


    public function testUpdateCommitteeFail()
    {
        $committee = $this->createCommittee();
        $updatedCommittee = [
            'icon' => '<i class="fas fa-piggy-bank"></i>',
        ];
        $response = $this->postJson('/api/21/v1/commitee/' . $committee['id'], $updatedCommittee, [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->whereType('error', 'array')
                    ->whereType('error.name', 'array')
            );
    }


    public function testDeleteCommitteeSuccess()
    {
        $committee = $this->createCommittee();

        $response = $this->deleteJson('/api/21/v1/commitee/' . $committee['name'], [], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200);
    }



    public function testDeleteCommitteeFail()
    {

        $response = $this->deleteJson('/api/21/v1/commitee/-45', [], [
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
