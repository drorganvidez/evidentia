<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificateTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        config(['database.connections.instance' => [
            'driver'   => 'mysql',
            'host' => env('DB_HOST'),
            'database' => 'base21',
            'port' => env('DB_PORT'),
            'username' => env('DB_USERNAME'),
            'password' => 'secret'
        ]]);
    
        config(['database.default' => 'instance']);

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('html');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certificate');
    }
}
