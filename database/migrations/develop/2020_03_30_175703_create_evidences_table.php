<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvidencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('comittee_id');
            $table->string('title');
            $table->text('description');
            $table->integer('hours');
            $table->integer('points_to')->nullable($value = true);
            $table->enum('status', ['DRAFT', 'PENDING', 'ACCEPTED', 'REJECTED', 'BIN', 'CLOSED']);
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
        Schema::dropIfExists('evidences');
    }
}
