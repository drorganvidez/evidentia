<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registro de actividad solo de inserción; nombres, títulos y comités copiados porque los usuarios se borran cada curso
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at');
            $table->uuid('request_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_username')->nullable();
            $table->string('acting_role', 24)->nullable()->index();
            $table->string('action', 48);
            $table->string('subject_type', 32)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('subject_root_id')->nullable();
            $table->string('subject_title')->nullable();
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->string('target_name')->nullable();
            $table->string('target_username')->nullable();
            $table->unsignedBigInteger('committee_id')->nullable();
            $table->string('committee_name')->nullable();
            $table->json('details')->nullable();
            $table->json('flags')->nullable();
            $table->boolean('flagged')->default(false);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
            $table->index(['target_user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['subject_type', 'subject_root_id']);
            $table->index(['committee_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['flagged', 'created_at']);
        });

        // Afectados múltiples de una anotación (asistentes, beneficiarios, miembros, audiencia, importados)
        Schema::create('activity_log_targets', function (Blueprint $table) {
            $table->foreignId('activity_log_id')->constrained('activity_log')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->index();
            $table->primary(['activity_log_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log_targets');
        Schema::dropIfExists('activity_log');
    }
};
