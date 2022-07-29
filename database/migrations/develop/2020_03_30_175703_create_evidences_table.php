<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvidencesTable extends Migration
{

    protected $connection = 'base21';
    
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
            $table->foreignId('committee_id')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->float('hours')->nullable();
            $table->integer('points_to')->nullable($value = true);
            $table->enum('status', ['DRAFT', 'PENDING', 'ACCEPTED', 'REJECTED', 'BIN', 'CLOSED'])->nullable();;
            $table->string('stamp')->nullable($value = true);
            $table->boolean('last')->default(true);
            $table->boolean('rand')->default(false);
            $table->boolean('temp')->default(true);
            $table->boolean('autosaved')->default(false);
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
