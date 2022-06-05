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
            config(['database.connections.instance' => [
                'driver' => 'mysql',
                'host' => 'mysql',
                'database' => 'base21',
                'port' => '3306',
                'username' => 'evidentia',
                'password' => 'secret',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'engine' => 'InnoDB',
            ]]);

            config(['database.default' => 'instance']);
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
