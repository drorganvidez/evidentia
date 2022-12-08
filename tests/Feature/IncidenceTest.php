<?php

namespace Tests\App\Http\Controllers;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class IncidenceTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

    //use RefreshDatabase;

    public function testSettingUp() :void {

        DB::connection()->getPdo()->exec("DROP DATABASE IF EXISTS `evidentia`;");
        DB::connection()->getPdo()->exec("CREATE DATABASE IF NOT EXISTS `evidentia`");
        DB::connection()->getPdo()->exec("ALTER SCHEMA `evidentia`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");
        exec("php artisan migrate");
        exec("php artisan db:seed");
        exec('php artisan db:seed --class=InstancesTableSeeder');

        $this->assertTrue(true);

    }

    public function testLoginAlumno(){
        $request = [
            'username' => 'alumno1',
            'password' => 'alumno1'
        ];
        $response = $this->post('login_p',$request);
        $response->assertSessionDoesntHaveErrors();

    }

    public function testListIncidence()
    {  
        $this->testLoginAlumno();

        $response = $this->get('/incidence/list');
        $response->assertStatus(302);
    }

    public function testCreateIncidence()
    {
        
        $this->testLoginAlumno();

        $request = [
            'title' => 'Test incidence title',
            'description' => 'Test incidence description',
            'comittee_id' => '1',
        ];

        $response = $this->post('/incidence/create/',$request);

        $response->assertStatus(302);
    }
    public function testLoginCoordinador(){
        $request = [
            'username' => 'coordinador1',
            'password' => 'coordinador1'
        ];
        $response = $this->post('login_p',$request);
        $response->assertSessionDoesntHaveErrors();

    }
    public function testListIncidenceCoordinador()
    {  
        $this->testLoginCoordinador();

        $response = $this->get('/coordinador/incidence/list');
        $response->assertStatus(302);
    }

}
