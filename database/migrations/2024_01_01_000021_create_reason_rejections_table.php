<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reason_rejections', function (Blueprint $table) {
            $table->id();
            $table->text('reason');
            $table->foreignId('evidence_id')->constrained('evidences')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reason_rejections');
    }
};
