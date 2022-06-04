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

    private AssertableJsonString $user;

    /**
     * @throws Throwable
     */
    public function setUp() : void
    {
        parent::setUp();
        $this->createUser();
    }

    /**
     * @throws Throwable
     */
    public function tearDown() : void
    {
        $this->deleteUser($this->user);
    }

    /**
     * @throws Throwable
     */
    protected function createUser() : AssertableJsonString
    {
        $user = $this->postJson('/api/21/v1/user', [
            'username' => 'alumno3',
            'password' => 'alumno3',
            'name' => 'Nuevo alumno',
            'surname' => 'alumno3',
            'email' => 'alumno3@alumno3.com',
            'block' => false,
            'biography' => 'Lorem ipsum dolor sit amet',
            'clean_name' => 'alumno3',
            'clean_surname' => 'alumno3'
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ])->decodeResponseJson();

        $this->user = $user;

        return $user;
    }

    /**
     * @throws Throwable
     */
    public function deleteUser($user)
    {

        $this->deleteJson('/api/21/v1/user/' . $user['id'], [], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

    }

    /**
     * @throws Throwable
     */
    public function testGetUserSuccess()
    {

        $response = $this->getJson('/api/21/v1/user/' . $this->user['id'], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'surname' => $this->user['surname'],
                    'name' => $this->user['name'],
                    'username' => $this->user['username'],
                    'email' => $this->user['email'],
                    'block' => $this->user['block'],
                    'biography' => $this->user['biography'],
                    'clean_name' => $this->user['clean_name'],
                    'clean_surname' => $this->user['clean_surname'],
                ]
            );

    }

    /**
     * @throws Throwable
     */
    public function testCreateUserSuccess()
    {
        $response = $this->postJson('/api/21/v1/user', [
            'username' => 'alumnonuevo',
            'password' => 'alumnonuevo',
            'name' => 'alumnonuevo',
            'surname' => 'alumnonuevo',
            'email' => 'alumnonuevo@alumnonuevo.com',
            'block' => 0,
            'biography' => 'alumnonuevo',
            'clean_name' => 'alumnonuevo',
            'clean_surname' => 'alumnonuevo'
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $newUser = $response->decodeResponseJson();

        $response
            ->assertStatus(200)
            ->assertJson([
                'username' => 'alumnonuevo',
                'name' => 'alumnonuevo',
                'surname' => 'alumnonuevo',
                'email' => 'alumnonuevo@alumnonuevo.com',
                'block' => 0,
                'biography' => 'alumnonuevo',
                'clean_name' => 'alumnonuevo',
                'clean_surname' => 'alumnonuevo'
            ]);

        $this->deleteUser($newUser);

    }

    /**
     * @throws Throwable
     */
    public function testCreateUserFail()
    {
        $response = $this->postJson('/api/21/v1/user', [
            'username' => 'alumno3',
            'password' => 'alumno3',
            'surname' => 'alumno3',
            'email' => 'alumno3@alumno3.com',
            'block' => false,
            'biography' => 'Lorem ipsum dolor sit amet',
            'clean_name' => 'alumno3',
            'clean_surname' => 'alumno3'
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

    /**
     * @throws Throwable
     */
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

        $updatedUser = [
            'username' => 'alumno3EDIT',
            'password' => 'alumno3EDIT',
            'name' => 'Nuevo alumnoEDIT',
            'surname' => 'alumno3EDIT',
            'email' => 'alumno3EDIT@alumno3.com',
            'block' => true,
            'biography' => 'Lorem ipsum dolor sit ametEDIT',
            'clean_name' => 'alumno3EDIT',
            'clean_surname' => 'alumno3EDIT'
        ];

        $response = $this->putJson('/api/21/v1/user/' . $this->user['id'], $updatedUser, [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'username' => $updatedUser['username'],
                    'name' => $updatedUser['name'],
                    'surname' => $updatedUser['surname'],
                    'email' => $updatedUser['email'],
                    'block' => $updatedUser['block'],
                    'biography' => $updatedUser['biography'],
                    'clean_name' => $updatedUser['clean_name'],
                    'clean_surname' => $updatedUser['clean_surname'],
                ]
            );

    }

    /**
     * @throws Throwable
     */
    public function testUpdateUserFail()
    {

        $updatedUser = [
            'username' => 'alumno3',
            'password' => 'alumno3',
            'name' => 'Nuevo alumno',
            'surname' => 'alumno3',
            'email' => 'emailerroneo',
            'block' => false,
            'biography' => 'Lorem ipsum dolor sit amet',
            'clean_name' => 'alumno3',
            'clean_surname' => 'alumno3'
        ];

        $response = $this->putJson('/api/21/v1/user/' . $this->user['id'], $updatedUser, [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->whereType('error', 'array')
                    ->whereType('error.email', 'array')
            );

    }

    /**
     * @throws Throwable
     */
    public function testDeleteUserSuccess()
    {

        $response = $this->deleteJson('/api/21/v1/user/' . $this->user['id'], [], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $response->assertStatus(200);

    }

    /**
     * @throws Throwable
     */
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




