<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstanceTest extends TestCase{

    public function testSettingUp() :void {

        DB::connection()->getPdo()->exec("DROP DATABASE IF EXISTS `evidentia`;");
        DB::connection()->getPdo()->exec("CREATE DATABASE IF NOT EXISTS `evidentia`");
        DB::connection()->getPdo()->exec("ALTER SCHEMA `evidentia`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");
        exec("php artisan migrate");
        exec("php artisan db:seed");
        exec('php artisan db:seed --class=InstancesTableSeeder');

        $this->assertTrue(true);

    }

    private function loginWithProfesor1(){
        $request = [
            'username' => 'profesor1',
            'password' => 'profesor1'
        ];
        $response = $this->post('login',$request);
    }

    public function testEstadisticsDashboard()
    {
        
        $this->loginWithProfesor1();

        $response = $this->get('/21/lecture/dashboard');
        $response->assertStatus(302);
    }

}