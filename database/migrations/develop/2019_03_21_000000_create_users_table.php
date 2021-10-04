<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
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
            'host' => 'localhost',
            'database' => 'base21',
            'port' => '33060',
            'username' => 'homestead',
            'password' => 'secret'
        ]]);

        config(['database.default' => 'instance']);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('username')->unique();;
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('biography')->nullable();
            $table->enum('participation',['ORGANIZATION','INTERMEDIATE','ASSISTANCE'])->default('ASSISTANCE');
            $table->boolean('block')->default(0);
            $table->string('clean_name')->default('');
            $table->string('clean_surname')->default('');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
