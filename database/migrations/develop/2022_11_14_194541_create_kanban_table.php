<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanbanTable extends Migration
{
    protected $connection = 'base21';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kanban', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('user_id');
            $table->foreignId('comittee_id');
            $table->boolean('last')->default(true);
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
        Schema::dropIfExists('kanban');
    }
}
