<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class CreateIncidenceTable extends Migration
{

    protected $connection = 'base21';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('comittee_id');
            $table->string('title');
            $table->text('description');
            $table->text('close_reason');
            $table->enum('status', ['PENDING', 'INREVIEW', 'CLOSED']);
            $table->string('stamp')->nullable($value = true);
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
        Schema::dropIfExists('riesgos');
    }
}
