<?php

namespace Tests\Feature\Api\v1;

use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

class AuthTest extends TestCase
{

    /**
     * Functional testing for the authentication module of the REST API.
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

    public function testLoginSuccess()
    {
        $response = $this->postJson('/api/21/v1/auth/login', [
            'email' => 'alumno1@alumno1.com',
            'password' => 'alumno1'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->whereType('token', 'string')
            );
    }

    public function testLoginInvalidMailFail()
    {
        $response = $this->postJson('/api/21/v1/auth/login', [
            'email' => 'alumno1@alumno.com',
            'password' => 'alumno1'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('status', false)
                ->where('error', 'Invalid credentials')
            );
    }

    public function testLoginInvalidPasswordFail()
    {
        $response = $this->postJson('/api/21/v1/auth/login', [
            'email' => 'alumno1@alumno1.com',
            'password' => 'alumno2'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('status', false)
                ->where('error', 'Invalid credentials')
            );
    }

    public function testLoginInvalidDataFail()
    {
        $response = $this->postJson('/api/21/v1/auth/login', [
            'email' => 'alumno1@alumno1.com',
            'password' => 234234
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('status', false)
                ->whereType('error', 'array')
                ->whereType('error.password', 'array')
            );
    }

    public function testLogoutSuccess()
    {

        $response = $this->postJson('/api/21/v1/auth/logout', [], [
            'Authorization' => 'Bearer '.$this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
            );
    }

    public function testLogoutFail()
    {
        $response = $this->getJson('/api/21/v1/auth/me');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('status', 'Authorization token not found')
            );
    }

    public function testMeSuccess()
    {

        $response = $this->getJson('/api/21/v1/auth/me',  [
            'Authorization' => 'Bearer '.$this->login()
        ]);

        $response
            ->assertStatus(200);
    }

    public function testMeFail()
    {
        $response = $this->getJson('/api/21/v1/auth/me');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('status', 'Authorization token not found')
            );
    }

    public function testRefreshSuccess()
    {
        $response = $this->postJson('/api/21/v1/auth/refresh', [], [
            'Authorization' => 'Bearer '.$this->login()
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->whereType('token', 'string')
            );
    }

    public function testRefreshFail()
    {
        $response = $this->postJson('/api/21/v1/auth/refresh');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('status', 'Authorization token not found')
            );
    }

}