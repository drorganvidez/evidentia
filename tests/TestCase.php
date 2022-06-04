<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @throws Throwable
     */
    protected function login($email = 'alumno1@alumno1.com', $password = 'alumno1') : String
    {
        return $this->postJson('/api/21/v1/auth/login', [
            'email' => $email,
            'password' => $password
        ])->decodeResponseJson()['token'];
    }
}
