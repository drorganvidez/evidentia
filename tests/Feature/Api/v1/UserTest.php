<?php

namespace Tests\Feature\Api\v1;

use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use \Illuminate\Testing\AssertableJsonString;
use Throwable;

class UserTest extends TestCase
{
    /**
     * Functional testing for the users of the REST API.
     *
     * @return void
     */

    public function setUp() : void {
        parent::setUp();
    }

    /**
     * @throws Throwable
     */
    private function createUser() : AssertableJsonString
    {
        return $this->putJson('/api/21/v1/user', [
            'surname' => 1,
            'name' => 'alum34',
            'username' => 'alum1999999',
            'password' => 324322,
            'email' => 'alumno1999999@alumno1.com',
            'block' => false,
            'biography' => 'alumno3',
            'clean_name' => 'alum3',
            'clean_surname' => 'alum3'
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ])->decodeResponseJson();
    }

    public function deleteUser($user){

        $this->deleteJson('/api/21/v1/user/' . $user['id'], [], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

    }

    public function testCreateUserSuccess()
    {
        $response = $this->putJson('/api/21/v1/user', [
            'surname' => 1,
            'name' => 'alum3',
            'username' => 'alum4',
            'password' => 324322,
            'email' => 'alumno5@alumno1.com',
            'block' => 0,
            'biography' => 'alumno3',
            'clean_name' => 'alum3',
            'clean_surname' => 'alum3'
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'surname' => 1,
                'name' => 'alum3',
                'username' => 'alum4',
                'email' => 'alumno5@alumno1.com',
                'block' => 0,
                'biography' => 'alumno3',
                'clean_name' => 'alum3',
                'clean_surname' => 'alum3'
            ]);
        $this -> deleteUser($response);

    }

    public function testCreateUserFail()
    {
        $response = $this->putJson('/api/21/v1/user', [
            'surname' => 1,

            'username' => 'alum3',
            'password' => 324322,
            'email' => 'alumno1232@alumno1.com',
            'block' => 0,
            'biography' => 'alumno3',
            'clean_name' => 'alum3',
            'clean_surname' => 'alum3'
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

    public function testGetAllUsersSuccess()
    {
        $response = $this->getJson('/api/21/v1/user', [
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

    public function testGetAllUsersFail()
    {
        $response = $this->getJson('/api/21/v1/user');

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->where('status', 'Authorization token not found')
            );
    }


    /**
     * @throws Throwable
     */
    public function testGetUserSuccess()
    {
        $user = $this->createUser();

        $response = $this->getJson('/api/21/v1/user/' . $user['id'], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                [

                    'surname' => $user['surname'],
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'block' => $user['block'],
                    'biography' => $user['biography'],
                    'clean_name' => $user['clean_name'],
                    'clean_surname' => $user['clean_surname'],

                ]
            );
        $this -> deleteUser($user);
    }

    public function testGetUserFail()
    {

        $response = $this->getJson('/api/21/v1/user/-1', [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @throws Throwable
     */
    public function testUpdateUserSuccess()
    {
        $user = $this->createUser();
        $updatedUser = [
            'surname' => 3,
            'name' => 'alumno',
            'username' => 'alumno2002',
            'password' => 4,
            'email' => 'alumno2002@alumno.com',
            'block' => false,
            'biography' => 'alumno',
            'clean_name' => 'alumno',
            'clean_surname' => 'alumno'
        ];
        $response = $this->postJson('/api/21/v1/user/' . $user['id'], $updatedUser, [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'surname' => $updatedUser['surname'],
                    'name' => $updatedUser['name'],
                    'username' => $updatedUser['username'],
                    'email' => $updatedUser['email'],
                    'block' => $updatedUser['block'],
                    'biography' => $updatedUser['biography'],
                    'clean_name' => $updatedUser['clean_name'],
                    'clean_surname' => $updatedUser['clean_surname'],
                ]
            );
        $this -> deleteUser($response);
    }

    /**
     * @throws Throwable
     */
    public function testUpdateUserFail()
    {
        $user = $this->createUser();
        $updatedUser = [
            'surname' => 3,
            'name' => 'alumno2002',
            'username' => 'alumno2002',
            'password' => 4,
            'block' => 0,
            'biography' => 'alumno',
            'clean_name' => 'alumno',
            'clean_surname' => 'alumno'
        ];
        $response = $this->postJson('/api/21/v1/user/' . $user['id'], $updatedUser, [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->whereType('error', 'array')
                    ->whereType('error.email', 'array')
            );
        $this -> deleteUser($user);
    }


    /**
     * @throws Throwable
     */
    public function testDeleteUserSuccess()
    {
        $user = $this->createUser();

        $response = $this->deleteJson('/api/21/v1/user/' . $user['id'], [], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200);


        $this -> deleteUser($user);
    }



    public function testDeleteUserFail()
    {

        $response = $this->deleteJson('/api/21/v1/user/-45', [], [
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




