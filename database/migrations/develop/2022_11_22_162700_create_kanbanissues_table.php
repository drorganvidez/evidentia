<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanbanIssuesTable extends Migration
{
    protected $connection = 'base21';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kanban_issues', function (Blueprint $table) {
            $table->id();
            $table->string('task');
            $table->string('description');
            $table->float('hours');
            $table->foreignId('user_id');
            $table->foreignId('comittee_id');
            $table->enum('type',['TODO','INPROGRESS','CLOSED']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kanban_issues');
    }
}