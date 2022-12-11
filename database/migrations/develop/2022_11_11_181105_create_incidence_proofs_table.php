<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncidenceProofsTable extends Migration
{
    protected $connection = 'base21';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incidence_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incidence_id')->references('id')
                                                ->on('incidence')->onDelete('cascade');
            $table->foreignId('file_id')->references('id')
                                                ->on('files')->onDelete('cascade');
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
        Schema::dropIfExists('incidence_proofs');
    }
}
