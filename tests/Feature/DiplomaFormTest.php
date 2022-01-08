<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DiplomaFormTest extends TestCase
{

    public function testSettingUp() :void 
    {
        exec("php artisan evidentia:createinstance");

        $this->assertTrue(true);
    }

    public function testCoordinatorLoginTrue()
    {
        $request = [
            'username' => 'coordinador1',
            'password' => 'coordinador1'
        ];
        $response = $this->post('/login_p',$request);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(route('home',""));
    }

    public function testFormCorrect(){

        $this->testCoordinatorLoginTrue();
        

        $request = [
            'nombreDiploma' => 'TestDiploma',
            'name' => 'Roberto',
            'mailto' => 'miguenieva2000@gmail.com',
            'course' => 'Ciberseguridad',
            'score' => 'nº1',
            'diplomaGenerar' => 'Diploma Comité Registro',
            'date'=>'01/01/2022'
        ];
        $response = $this->get('/coordinator/certificate',$request);
        $response -> assertValid();
        $response -> assertStatus(302);
    }

    public function testMailIncorrect(){
    
        $this->testCoordinatorLoginTrue();

        $request = [
        'nombreDiploma' => 'TestDiploma',
        'name' => 'Roberto',
        'mailto' => 'incorrect',
        'course' => 'Ciberseguridad',
        'score' => 'nº1',
        'diplomaGenerar' => 'Diploma Comité Registro',
        'date'=>'01/01/2022'
        ];

        $response = $this->get('/coordinator/certificate',$request);
        ($response -> assertValid('mailto'))==false;
    }

    public function testDateIncorrect(){
    
        $this->testCoordinatorLoginTrue();

        $request = [
            'nombreDiploma' => 'TestDiploma',
            'name' => 'Roberto',
            'mailto' => 'miguenieva2000@gmail.com',
            'course' => 'Ciberseguridad',
            'score' => 'nº1',
            'diplomaGenerar' => 'Diploma Comité Registro',
            'date'=>'01/43/3412'
        ]   ;

        $response = $this->get('/coordinator/certificate',$request);
        ($response -> assertValid('date'))==false;
    }

    public function testDiplomaGenerarIncorrect(){
        
        $this->testCoordinatorLoginTrue();
        $request = [
            'nombreDiploma' => 'TestDiploma',
            'name' => 'Roberto',
            'mailto' => 'miguenieva2000@gmail.com',
            'course' => 'Ciberseguridad',
            'score' => 'nº1',
            'diplomaGenerar' => 'Diploma Comité Registro',
            'date'=>'01/43/3412'
        ]   ;

        $response = $this->get('/coordinator/certificate',$request);
        ($response->assertSessionHasNoErrors())==false;
    }

    public function testFormIncorrect(){
        
        $this->testCoordinatorLoginTrue();
        $request = [
            'nombreDiploma' => '',
            'name' => '',
            'mailto' => '',
            'course' => '',
            'score' => '',
            'diplomaGenerar' => '',
            'date'=>''
        ]   ;

        $response = $this->post('/coordinator/certificate',$request);
        ($response->assertSessionHasNoErrors())==false;
    }

    public function testScoreEmpty(){

        $this->testCoordinatorLoginTrue();
        $request = [
            'nombreDiploma' => 'TestDiploma',
            'name' => 'Roberto',
            'mailto' => 'miguenieva2000@gmail.com',
            'course' => 'Ciberseguridad',
            'score' => '',
            'diplomaGenerar' => 'Diploma Comité Registro',
            'date'=>'01/43/3412'
        ]   ;

    $response = $this->get('/coordinator/certificate',$request);
    ($response -> assertValid('score'))==false;
    }
}