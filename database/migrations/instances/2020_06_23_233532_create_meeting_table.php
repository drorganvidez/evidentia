<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comittee_id')->nullable(true);
            $table->string('title');
            $table->timestamp('datetime')->nullable(true);
            $table->string('place');
            $table->enum('type',['ORDINARY','EXTRAORDINARY']);
            $table->enum('modality', ['F2F', 'TELEMATIC', 'MIXED','OTHER']);
            $table->float('hours');
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
        Schema::dropIfExists('meeting');
    }
}
