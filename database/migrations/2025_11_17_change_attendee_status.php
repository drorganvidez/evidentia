<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            // Cambiar ENUM → string(50)
            $table->string('status', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            // Si quieres volver atrás (NO lo vas a querer)
            $table->enum('status', ['Attending', 'Checked In', 'Cancelled'])->default('Attending')->change();
        });
    }
};
