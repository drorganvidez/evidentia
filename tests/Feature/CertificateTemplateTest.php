<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CertificateTemplateTest extends TestCase
{
    use WithoutMiddleware;

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
        $response = $this->post('21/login_p',$request);

        $response->assertRedirect(route('home',""));
    }

    public function testCreateSuccess()
    {
        config(['database.connections.instance' => [
            'driver'   => 'mysql',
            'host' => 'mysql',
            'database' => 'base21',
            'port' => '3306',
            'username' => 'evidentia',
            'password' => 'secret'
        ]]);
    
        config(['database.default' => 'instance']);

        $request = [
            'title' => 'Test Plantilla Diploma',
            'html' => "<!DOCTYPE html> <html> <head> <style> @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap'); html,body{height: 70%;} body{ background-image: url('https://th.bing.com/th/id/R.ffeca8d50a4a2182120490bbd33c059d?rik=UI0cFlFDE0B8ZA&riu=http%3a%2f%2fwww.groupeamplify.com%2fuploads%2f2018%2f01%2fBusiness-Presentation-Skills.jpg&ehk=uFeLdzPvJRZHcH%2bOhA27rAA65BBm2VSYfSJXgPwwOUY%3d&risl=&pid=ImgRaw&r=0');       font-family: 'Poppins'; background-size: cover; font-weight: 300; height: 100%; text-align: center; color: #1BBC9B; text-shadow: 3px 3px 4px #000000; } .bold{ font-weight: 300; } </style> </head> <body> <div style= 'padding-top: 80px;'> <div> <h1 class='bold'>DIPLOMA DE PONENCIA</h1> <p style='margin-bottom: 0px;margin-top: 25px;' class='light'>Agradece a:</p> <p style='font-size: 2.4em;color:#1BBC9B;margin:0px' class='bold'>{{name}}</p> <br> <br> <p style='margin:0px;'>Por realizar la ponencia:</p> <h2 style='max-width: 600px;margin:25px auto;font-size: 3em;' class='bold'>{{course}}</h2> <!-- <p style='margin:0px;'>Con la nota de:</p> <p style='font-size: 2.7em;' class='bold'>{{score}}</p> --> <br> <br> <br> <br> <p style='margin:0px;'>En la fecha:</p> <p style='font-size: 0.9em;' class='bold'>{{date}}</p> </div> </div> </body> </html>"
        ];
        $response = $this->call('GET', '21/coordinator/certificate/template', $request);
        #$response->dump();

        $response->assertStatus(302);
        $response->assertRedirect("21/coordinator/certificate/create");
    }
}