<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected bool $init = true;

    protected function init() : void
    {
        if($this->init){
            \Instantiation::set_default_connection();
            exec('php artisan evidentia:start docker');
            exec('php artisan evidentia:instance');
            $this->init = false;
        }
    }

    /**
     * @throws Throwable
     */
    protected function login($email = 'alumno1@alumno1.com', $password = 'alumno1')
    {
        return $this->postJson('/api/21/v1/auth/login', [
            'email' => $email,
            'password' => $password
        ])->decodeResponseJson()['token'];
    }
}
