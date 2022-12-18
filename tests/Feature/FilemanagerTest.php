<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Tests\TestCase;

class FilemanagerTest extends TestCase
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


    private function loginWithProfesor1()
    {
        $request = [
            'username' => 'profesor1',
            'password' => 'profesor1'
        ];
        $response = $this->post('login',$request);
    }

    private function loginWithAlumno1()
    {
        $request = [
            'username' => 'alumno1',
            'password' => 'alumno1'
        ];
        $response = $this->post('login', $request);
    }
    private function logout()
    {
        $this->post('/logout');
    }

    private function createFakeFile()
    {
        return UploadedFile::fake()->image('prueba.jpg')->size(100);
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testFilemanagerView()
    {
        $this->loginWithProfesor1();
        $response = $this->get('/lecture/user/1/filemanager');
        $response->assertStatus(302);
    }

    public function testPublishEvidenceWithProof()
    {
        $this->loginWithAlumno1();
        //Archivo para adjuntar
        $request = [
            'title' => 'test',
            'description' => 'description of test',
            'hours' => 1,
            'status' => 'PENDING',
            'user_id' => 1,
            'comittee_id' => 1,
            'files[]' => [$this->createFakeFile()]
        ];
        $this->post('/evidence/publish', $request)->assertStatus(302);
    }

    public function testDraftEvidenceWithProof()
    {
        $this->loginWithAlumno1();
        //Archivo para adjuntar
        $request = [
            'title' => 'test',
            'description' => 'description of test',
            'hours' => 1,
            'status' => 'DRAFT',
            'user_id' => 1,
            'comittee_id' => 1,
            'files[]' => [$this->createFakeFile()]
        ];
        $this->post('/evidence/draft', $request)->assertStatus(302);
    }
    public function testVerifyFile()
    {
        $this->loginWithAlumno1();
        //Archivo para adjuntar
        $request = [
            'title' => 'test',
            'description' => 'description of test',
            'hours' => 1,
            'status' => 'PENDING',
            'user_id' => 1,
            'comittee_id' => 1,
            'files[]' => [$this->createFakeFile()]
        ];
        $this->post('/evidence/publish', $request);
        $this->logout();
        $this->loginWithProfesor1();
        $this->get('/lecture/user/1/filemanager/evidence/1/proof/1/verify')->assertStatus(302);
    }

    public function testDeleteEvidenceWithVerifiedFile()
    {
        $this->loginWithAlumno1();
        //Archivo para adjuntar
        $request = [
            'title' => 'test',
            'description' => 'description of test',
            'hours' => 1,
            'status' => 'PENDING',
            'user_id' => 1,
            'comittee_id' => 1,
            'files[]' => [$this->createFakeFile()]
        ];
        $this->post('/evidence/publish', $request);
        $this->logout();
        $this->loginWithProfesor1();
        $this->get('/lecture/user/1/filemanager/evidence/1/proof/1/verify');
        $this->logout();
        $this->loginWithAlumno1();
        $this->post('/evidence/remove', ['id' => 1])->assertStatus(302);
    }


}
