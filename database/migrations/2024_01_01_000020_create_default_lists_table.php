<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('default_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('secretary_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        // Tabla pivote para relación muchos a muchos con usuarios
        Schema::create('default_list_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('default_list_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['default_list_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('default_list_user');
        Schema::dropIfExists('default_lists');
    }
};
