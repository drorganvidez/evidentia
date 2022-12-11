<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\Models\Task;
use Tests\Feature\TransactionCoordinatorTest;
class TransactionPresidentTest extends TestCase
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

    public function testLoginWithPresident1(){
        $request = [
            'username' => 'president1',
            'password' => 'president1'
        ];
        $response = $this->post('login',$request);
        $response->assertSessionDoesntHaveErrors();

    }

    public function testListTransactions()
    {  
        $this->testLoginWithPresident1();


        $response = $this->get('/president/transaction/list');
        $response->assertStatus(302);
    }

    public function testExportAllTransactionListPDF(){
        $this->testLoginWithPresident1();

        $response = $this->get('/transaction/export/all/pdf/');
        $response->assertStatus(302);
    }

    public function testExportAllTransactionListCSV(){
        $this->testLoginWithPresident1();

        $response = $this->get('/transaction/export/all/csv/');
        $response->assertStatus(302);
    }

    public function testExportAllTransactionListXLSX(){
        $this->testLoginWithPresident1();

        $response = $this->get('/transaction/export/all/xlsx/');
        $response->assertStatus(302);
    }

    public function testAceptedTest()
    {
        $request = [
            'username' => 'coordinador1',
            'password' => 'coordinador1'
        ];
        $response = $this->post('login',$request);
        $request = [
            'reason' => 'Test coordinator transaction',
            'type' => 'Beneficio',
            'amount' => '400',
            'comittee_id' => '1'
        ];

        $response = $this->post('/transaction/create/',$request);
        $response = $this->get('/logout');
        $this->testLoginWithPresident1();
        $response = $this->get('/president/transaction/list/');
        $response = $this->get('/president/transaction/accept/1');
        $response->assertStatus(302);
    }


    
    public function testDeniedTest()
    {
        $request = [
            'username' => 'coordinador1',
            'password' => 'coordinador1'
        ];
        $response = $this->post('login',$request);
        $request = [
            'reason' => 'Test coordinator transaction',
            'type' => 'Beneficio',
            'amount' => '400',
            'comittee_id' => '1'
        ];

        $response = $this->post('/transaction/create/',$request);
        $response = $this->get('/logout');
        $this->testLoginWithPresident1();
        $response = $this->get('/president/transaction/list/');
        $response = $this->get('/president/transaction/reject/2');
        $response->assertStatus(302);
    }
}