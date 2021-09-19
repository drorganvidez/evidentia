<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting_request', function (Blueprint $table) {
            $table->id();
            $table->timestamp('datetime')->nullable(true);
            $table->string('place');
            $table->enum('type',['ORDINARY','EXTRAORDINARY']);
            $table->enum('modality', ['F2F', 'TELEMATIC', 'MIXED','OTHER']);
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
        Schema::dropIfExists('meeting_request');
    }
}
