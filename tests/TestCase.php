<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected bool $init = true;

    protected function setDefaultInstanceConnection() : void
    {
        if($this->init){
            config(['database.connections.instance' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST'),
                'database' => env('DB_DATABASE'),
                'port' => env('DB_PORT'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'engine' => 'InnoDB',
            ]]);

            config(['database.default' => 'instance']);
            $this->init = false;
        }
    }

    protected function setAppToZero()
    {
        DB::connection()->getPdo()->exec("DROP DATABASE IF EXISTS `evidentia`;");
        DB::connection()->getPdo()->exec("CREATE DATABASE IF NOT EXISTS `evidentia`");
        DB::connection()->getPdo()->exec("ALTER SCHEMA `evidentia`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");
        exec("php artisan migrate");
        exec("php artisan db:seed");
        exec('php artisan db:seed --class=InstancesTableSeeder');
    }
}
