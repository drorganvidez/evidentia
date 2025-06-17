<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->nullable()->constrained()->onDelete('set null');
            $table->dateTime('datetime')->nullable();
            $table->string('place')->nullable();
            $table->string('type')->nullable();
            $table->string('modality')->nullable();
            $table->foreignId('committee_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('secretary_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_requests');
    }
};
