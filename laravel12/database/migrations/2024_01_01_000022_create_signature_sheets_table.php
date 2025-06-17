<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('random_identifier')->nullable();
            $table->foreignId('meeting_request_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('secretary_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        // Tabla pivote para usuarios que firman la hoja
        Schema::create('signature_sheet_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signature_sheet_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['signature_sheet_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_sheet_user');
        Schema::dropIfExists('signature_sheets');
    }
};
