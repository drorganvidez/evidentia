<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_threads', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            // el hilo se borra con su autor
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('author_name');
            $table->string('author_role', 24);
            $table->string('recipient_role', 24);
            // solo para la coordinación y la secretaría de un comité
            $table->foreignId('committee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('about_type', 32)->nullable();
            $table->unsignedBigInteger('about_id')->nullable();
            $table->string('about_title')->nullable();
            $table->string('state', 12)->default('open');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_message_at');
            $table->unsignedBigInteger('last_message_by')->nullable();
            $table->timestamps();

            $table->index(['recipient_role', 'committee_id', 'state', 'last_message_at'], 'message_threads_audience_index');
            $table->index(['author_id', 'last_message_at']);
            $table->index(['about_type', 'about_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_thread_id')->constrained()->cascadeOnDelete();
            // un responsable borrado no se lleva sus respuestas
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name');
            $table->string('role', 24)->nullable();
            $table->text('body');
            $table->timestamps();
            $table->index(['message_thread_id', 'created_at']);
        });

        Schema::create('message_thread_reads', function (Blueprint $table) {
            $table->foreignId('message_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->primary(['message_thread_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_thread_reads');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('message_threads');
    }
};
