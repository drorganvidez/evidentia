<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Students who did an evidence along with its author; each version keeps its own list. */
    public function up(): void
    {
        Schema::create('evidence_coauthors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_id')->constrained('evidences')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['evidence_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_coauthors');
    }
};
