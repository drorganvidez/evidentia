<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diary_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diary_id')->constrained()->onDelete('cascade');
            $table->text('point');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diary_points');
    }
};
