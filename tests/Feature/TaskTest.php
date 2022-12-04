<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\Models\Task;

class TaskTest extends TestCase
{

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

    public function testLoginWithAlumno1(){
        $request = [
            'username' => 'alumno1',
            'password' => 'alumno1'
        ];
        $response = $this->post('login',$request);
        $response->assertSessionDoesntHaveErrors();

    }

    public function testListTask()
    {
        
        $this->testLoginWithAlumno1();


        $response = $this->get('/21/task/list/');
        $response->assertStatus(302);
    }

    public function testEditTask()
    {
        
        $this->testLoginWithAlumno1();

        $request = [
            'id'    => 1,
            'title' => 'Task 1',
            'description' => 'Modificación de un test exitoso',
            'hours' => '1.0',
            'end_date' => '2022-11-18 08:26',
            'user_id' => '2022-11-18 09:26',
            'comittee_id' => '2'
        ];
        
        $response = $this->post('/21/task/edit/save',$request);

        $response->assertStatus(302);
    }

    public function testDetailTaskView(){
        $this->testLoginWithAlumno1();

        $response = $this->get('/21/task/view/1');
        $response->assertStatus(302);
    }

    public function testExportTaskListPDF(){
        $this->testLoginWithAlumno1();

        $response = $this->get('/21/task/list/export/{ext}');
        $response->assertStatus(302);
    }

    public function testCreateTaskPositive()
    {
        
        $this->testLoginWithAlumno1();

        $request = [
            'title' => 'Evidencia 2',
            'description' => 'Creación de un test exitoso',
            'hours' => '1.0',
            'end_date' => '2022-11-18 08:26',
            'user_id' => '2022-11-18 09:26',
            'comittee_id' => '2'
        ];

        $response = $this->post('/21/task/create',$request);

        $response->assertStatus(302);
    }

    public function testCreateTaskNegative()
    {
        
        $this->testLoginWithAlumno1();

        $request = [
            'title' => 'Evidencia 2',
            'description' => '',
            'hours' => '1.0',
            'end_date' => '2022-11-18 08:26',
            'user_id' => '2022-11-18 09:26',
            'comittee_id' => '2'
        ];

        $response = $this->post('/21/task/create',$request);

        $response->assertStatus(302);
    }


}
