<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\Models\Task;

class KanbanTest extends TestCase
{
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

    public function testLoginWithCoordinator1(){
        $request = [
            'username' => 'coordinador1',
            'password' => 'coordinador1'
        ];
        $response = $this->post('login',$request);
        $response->assertSessionDoesntHaveErrors();

    }

    public function testListKanban()
    {
        
        $this->testLoginWithAlumno1();


        $response = $this->get('/21/kanban/list/');
        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();
    }

    public function testDetailKanbanView(){
        $this->testLoginWithAlumno1();

        $response = $this->get('/21/kanban/view/1');

        $response->assertSessionDoesntHaveErrors();
        $response->assertStatus(302);
    }


    public function testNewKanbanPositive()
    {
        
        $this->testLoginWithCoordinator1();
        $user = Auth::user();
        $request = [
            'title' => 'Kanban Test',
            'user_id' => $user,
            'comittee_id' => '1'
        ];

       
        $response = $this->post('/21/kanban/new',$request);
        
        $response->assertStatus(302);
    }
    
    public function testCreateKanban(){
        $this->testLoginWithAlumno1();

        $response = $this->get('/21/kanban/create'); ##Un alumno no debería poder acceder a esta url

        $response->assertSessionDoesntHaveErrors();
        $response->assertStatus(302);
    }

    public function testNewKanbanNegative()
    {
        
        $this->testLoginWithCoordinator1();
        $user = Auth::user();
        $request = [
            'title' => 'Quad', ##El titulo en principio debería ser mínimo de 5 caracteres
            'user_id' => $user,
            'comittee_id' => '2'
        ];

        $response = $this->post('/21/kanban/new',$request);

        $response->assertStatus(302);
    }

    public function testNewKanbanWithoutLogin()
    {
        $user = Auth::user();
        $request = [
            'title' => 'Intento de creación de Tablero sin estar logueado',
            'user_id' => $user,
            'comittee_id' => '1'
        ];

        $response = $this->post('/21/kanban/new',$request);

        $response->assertStatus(302);
    }

    public function testNewKanbanLoginWithAlumno1()
    {
        $this->testLoginWithAlumno1();
        $user = Auth::user();
        $request = [
            'title' => 'Intento de creación de Tablero siendo alumno',
            'user_id' => $user,
            'comittee_id' => '1'
        ];

        $response = $this->post('/21/kanban/new',$request);

        $response->assertStatus(302);
    }

    public function testDeleteKanban()
    {
        $this->testLoginWithAlumno1();
        $user = Auth::user();
        $request = [
            'id' => '1'
        ];

        $response = $this->post('/21/kanban/remove',$request);

        $response->assertStatus(302);
    } 

    public function testDeleteKanbanWithoutLogin()
    {
        $user = Auth::user();
        $request = [
            'id' => '1'
        ];

        $response = $this->post('/21/kanban/remove',$request);

        $response->assertStatus(302);
    } 

    public function testNewIssuePositive()
    {
        
        $this->testLoginWithAlumno1();
        $user = Auth::user();
        $request = [
            'title' => 'Issue Test',
            'description' => 'This is a test issue',
            'estimated_hours' => '1',
            'kanban_id' => '1'
        ];

       
        $response = $this->post('/21/kanban/view/1/issue/new',$request);
        
        $response->assertStatus(302);
    }

    public function testNewIssueNegative()
    {
        
        $this->testLoginWithCoordinator1();
        $user = Auth::user();
        $request = [
            'title' => 'Quad', ##El titulo en principio debería ser mínimo de 5 caracteres
            'description' => 'This is a test issue',
            'estimated_hours' => '1',
            'kanban_id' => '1'
        ];

        $response = $this->post('/21/kanban/view/1/issue/new',$request);

        $response->assertStatus(302);
    }

    public function testNewIssueWithoutLogin()
    {
        $user = Auth::user();
        $request = [
            'title' => 'Intento de creación de Issue sin estar logueado',
            'user_id' => $user,
            'comittee_id' => '1'
        ];

        $response = $this->post('/21/kanban/view/1/issue/new',$request);

        $response->assertStatus(302);
    }

}