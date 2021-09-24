<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignatureSheetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signature_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('random_identifier');
            $table->foreignId('meeting_request_id')->nullable();
            $table->foreignId('secretary_id');
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
        Schema::dropIfExists('signature_sheets');
    }
}
