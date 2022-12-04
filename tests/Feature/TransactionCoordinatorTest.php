<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\Models\Task;

class TransactionCoordinatorTest extends TestCase
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

    public function testLoginWithCoordinador1(){
        $request = [
            'username' => 'coordinador1',
            'password' => 'coordinador1'
        ];
        $response = $this->post('login',$request);
        $response->assertSessionDoesntHaveErrors();

    }

    public function testListTransactions()
    {  
        $this->testLoginWithCoordinador1();


        $response = $this->get('/transaction/list');
        $response->assertStatus(302);
    }

    public function testExportTransactionListPDF(){
        $this->testLoginWithCoordinador1();

        $response = $this->get('/transaction/export/mine/pdf/');
        $response->assertStatus(302);
    }

    public function testCreateTaskPositive()
    {
        
        $this->testLoginWithCoordinador1();

        $request = [
            'reason' => 'Test coordinator transaction',
            'type' => 'Beneficio',
            'amount' => '400',
            'comittee_id' => '1'
        ];

        $response = $this->post('/transaction/create/',$request);

        $response->assertStatus(302);
    }

    public function testCreateTaskNegative()
    {
        
        $this->testLoginWithCoordinador1();

        $request = [
            'reason' => '', // Es obligatorio
            'type' => 'Beneficio',
            'amount' => '400',
            'status' => 'PENDING',
            'comittee_id' => '1'
        ];

        $response = $this->post('/transaction/create/',$request);

        $response->assertStatus(302);
    }


}