<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestView;
use Tests\TestCase;

class SocialNetworkTest extends TestCase{

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

    public function testNewSocialNetwork(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Twitter',
            'password' => '123456789'
        ];

        $response = $this->post('/redesSociales/new',$request);
        $response->assertStatus(302);
    }

    public function testNewSocialNetworkNameEmpty(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => '',
            'password' => '123456789'
        ];

        $response = $this->post('/redesSociales/new',$request);
        $response->assertStatus(302)==false;
    }

    public function testNewSocialNetworkPassEmpty(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Twitter',
            'password' => ''
        ];

        $response = $this->post('/redesSociales/new',$request);
        $response->assertStatus(302)==false;
    }

    public function testUpdateSocialNetwork(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Twitter',
            'password' => '234567891'
        ];

        $response = $this->post('/redesSociales/save',$request);
        $response->assertStatus(302);
    }

    public function testUpdateSocialNetworkNameEmpty(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => '',
            'password' => '234567891'
        ];

        $response = $this->post('/redesSociales/save',$request);
        $response->assertStatus(302);
    }

    public function testUpdateSocialNetworkPassEmpty(){
        $this->testAdminLoginTrue();
        $request = [
            'name' => 'Twitter',
            'password' => ''
        ];

        $response = $this->post('/redesSociales/save',$request);
        $response->assertStatus(302);
    }

    public function testDeleteSocialNetwork(){
        $this->testAdminLoginTrue();
        $request = [
            'id' => '1'
        ];

        $response = $this->post('/redesSociales/remove',$request);
        $response->assertStatus(302);
    }
}