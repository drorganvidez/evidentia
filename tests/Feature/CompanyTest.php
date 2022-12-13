<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestView;
use Tests\TestCase;

class CompanyTest extends TestCase{

    public function testProfesorLoginTrue(){
        $request = [
            'username'=> 'profesor1',
            'password' => 'profesor1'
        ];

        $response = $this->post('/login_p',$request);
        $response->assertSessionDoesntHaveErrors();
    }

    public function testAdminLoginTrue(){
        $request = [
            'username'=> 'admin@admin.com',
            'password' => 'admin'
        ];

        $response = $this->post('/login_p',$request);
        $response->assertSessionDoesntHaveErrors();
    }

    public function testNewCompany(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Betis',
            'telephone' => '678564738',
            'email' => 'betis@hotmail.com',
        ];

        $response = $this->post('/empresasColaborativas/new',$request);
        $response->assertStatus(302);
    }

    public function testNewCompanyNameEmpty(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => '',
            'telephone' => '678564738',
            'email' => 'betis@hotmail.com',
        ];

        $response = $this->post('/empresasColaborativas/new',$request);
        $response->assertStatus(302)==false;
    }

    public function testNewCompanyPhoneEmpty(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Betis',
            'telephone' => '',
            'email' => 'betis@hotmail.com',
        ];

        $response = $this->post('/empresasColaborativas/new',$request);
        $response->assertStatus(302)==false;
    }

    public function testNewCompanyPhoneError(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Betis',
            'telephone' => 'sdfsdf',
            'email' => 'betis@hotmail.com',
        ];

        $response = $this->post('/empresasColaborativas/new',$request);
        $response->assertStatus(302)==false;
    }

    public function testNewCompanyPhoneErrorLength(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Betis',
            'telephone' => '67859483721',
            'email' => 'betis@hotmail.com',
        ];

        $response = $this->post('/empresasColaborativas/new',$request);
        $response->assertStatus(302)==false;
    }

    public function testNewCompanyMailEmpty(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Betis',
            'telephone' => '678564738',
            'email' => '',
        ];

        $response = $this->post('/empresasColaborativas/new',$request);
        $response->assertStatus(302)==false;
    }

    public function testNewCompanyMailError(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Betis',
            'telephone' => '678564738',
            'email' => 'sdfsdf',
        ];

        $response = $this->post('/empresasColaborativas/new',$request);
        $response->assertStatus(302)==false;
    }

    public function testUpdateCompany(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Betis',
            'telephone' => '678564738',
            'email' => 'betis@hotmail.com',
        ];

        $response = $this->post('/empresasColaborativas/save',$request);
        $response->assertStatus(302);
    }

    public function testUpdateCompanyNameEmpty(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => '',
            'telephone' => '678564738',
            'email' => 'betis@hotmail.com',
        ];

        $response = $this->post('/empresasColaborativas/save',$request);
        $response->assertStatus(302)==false;
    }

    public function testUpdateCompanyPhoneEmpty(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Betis',
            'telephone' => '',
            'email' => 'betis@hotmail.com',
        ];

        $response = $this->post('/empresasColaborativas/save',$request);
        $response->assertStatus(302)==false;
    }

    public function testUpdateCompanyPhoneError(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Betis',
            'telephone' => 'sdfsdf',
            'email' => 'betis@hotmail.com',
        ];

        $response = $this->post('/empresasColaborativas/save',$request);
        $response->assertStatus(302)==false;
    }

    public function testUpdateCompanyPhoneErrorLength(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Betis',
            'telephone' => '678594098324',
            'email' => 'betis@hotmail.com',
        ];

        $response = $this->post('/empresasColaborativas/save',$request);
        $response->assertStatus(302)==false;
    }

    public function testUpdateCompanyMailEmpty(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Betis',
            'telephone' => '675849374',
            'email' => '',
        ];

        $response = $this->post('/empresasColaborativas/save',$request);
        $response->assertStatus(302)==false;
    }

    public function testUpdateCompanyMailError(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Betis',
            'telephone' => '675849374',
            'email' => 'asdasd',
        ];

        $response = $this->post('/empresasColaborativas/save',$request);
        $response->assertStatus(302)==false;
    }

    public function testDeleteCompany(){
        $this->testAdminLoginTrue();
        $request = [
            'id' => '1'
        ];

        $response = $this->post('/empresasColaborativas/remove',$request);
        $response->assertStatus(302);
    }
}