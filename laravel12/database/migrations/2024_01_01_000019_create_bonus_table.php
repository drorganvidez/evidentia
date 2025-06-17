<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla principal 'bonuses'
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->string('reason');
            $table->float('hours')->default(0);
            $table->foreignId('committee_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        // Tabla pivote para relación muchos a muchos con usuarios
        Schema::create('bonus_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bonus_id')->constrained('bonuses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['bonus_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_user');
        Schema::dropIfExists('bonuses');
    }
};
