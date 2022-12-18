<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerifiedProofsTable extends Migration
{
    protected $connection = 'base21';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verified_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_id')->references('id')
                                            ->on('evidences')->onDelete('cascade');
            $table->integer('file_id');
            $table->string('name');
            $table->string('type');
            $table->string('size');
            $table->foreignId('lecturer_id')->references('id')
                                            ->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('verified_proofs');
    }
}
