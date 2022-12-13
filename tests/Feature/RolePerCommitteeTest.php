<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestView;
use Tests\TestCase;

class RolePerCommitteeTest extends TestCase{

    //Log In de los roles Presidente y Coordinador
    
    public function testPresidenteLoginTrue(){
        $request = [
            'username'=> 'presidente1',
            'password' => 'presidente1'
        ];

        $response = $this->post('/login_p',$request);
        $response->assertSessionDoesntHaveErrors();
    }

    public function testCoordinadorLoginTrue(){
        $request = [
            'username'=> 'coordinador1',
            'password' => 'coordinador1'
        ];

        $response = $this->post('/login_p',$request);
        $response->assertSessionDoesntHaveErrors();
    }

    // Operaciones POST Favorables

    public function testNewRoleWithCommitteeAssignation() {
        $this->testPresidenteLoginTrue();
        $request = [
            'rol' => 'Coordinador',
            'comittee' => 'Sostenibilidad',
            'user' => 'John Doe',
        ];

        $response = $this->post('/role/assignation',$request);
        $response->assertStatus(302);
    }

    public function testNewRoleAssignation() {
        $this->testPresidenteLoginTrue();
        $request = [
            'rol' => 'Estudiante',
            'user' => 'Tim Strong',
        ];

        $response = $this->post('/role/assignation',$request);
        $response->assertStatus(302);
    }

    // Operaciones POST Desfavorables

    public function testNewRoleWithEmptyCommitteeAssignation() {
        $this->testPresidenteLoginTrue();
        $request = [
            'rol' => 'Coordinador',
            'comittee' => '',
            'user' => 'John Doe',
        ];

        $response = $this->post('/role/assignation',$request);
        $response->assertStatus(302)==false;
    }

    public function testNewRoleEmptyAssignation() {
        $this->testPresidenteLoginTrue();
        $request = [
            'rol' => '',
            'user' => 'Tim Strong',
        ];

        $response = $this->post('/role/assignation',$request);
        $response->assertStatus(302)==false;
    }

    public function testNewRoleEmptyUserAssignation() {
        $this->testPresidenteLoginTrue();
        $request = [
            'rol' => 'Estudiante',
            'user' => '',
        ];

        $response = $this->post('/role/assignation',$request);
        $response->assertStatus(302)==false;
    }

    //Operacion GET como Coordinador: vista 'Miembros del Comite'

    public function testListCommittees()
    {
        $this->testCoordinadorLoginTrue();

        $response = $this->get('/coordinator/user/list');
        $response->assertStatus(302);
    }

    //Operacion GET como Presidente: vista 'Asignar Rol'

    public function testListRoles()
    {
        $this->testPresidenteLoginTrue();

        $response = $this->get('/president/rol/assignation');
        $response->assertStatus(302);
    }

}