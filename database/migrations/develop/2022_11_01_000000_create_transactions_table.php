<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{

    protected $connection = 'base21';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('comittee_id');
            $table->string('reason');
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED']);
            $table->enum('type', ['Beneficio', 'Gasto']);
            $table->float('amount');
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
        Schema::dropIfExists('transactions');
    }
}
