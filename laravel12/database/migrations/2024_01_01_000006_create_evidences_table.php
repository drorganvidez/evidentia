<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evidences', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->float('hours')->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('committee_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('points_to')->nullable();
            $table->enum('status', ['DRAFT', 'PENDING', 'ACCEPTED', 'REJECTED'])->default('DRAFT');
            $table->string('stamp')->nullable();
            $table->string('rand')->nullable();
            $table->timestamps();

            $table->foreign('points_to')->references('id')->on('evidences')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidences');
    }
};
