<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssueTable extends Migration
{
    protected $connection = 'base21';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kanban_id');
            $table->string('title');
            $table->text('description');
            $table->integer('estimated_hours');
            $table->enum('status', ['TO DO', 'IN PROGRESS', 'COMPLETED']);
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
        Schema::dropIfExists('issue');
    }
}
