<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Server errors grouped by where they happen (class, file and line), with their last occurrences, so the lecturer
 * finds out about every failure without reading the log on the server.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_reports', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint', 64)->unique();
            $table->string('exception');
            $table->text('message')->nullable();
            $table->string('file')->nullable();
            $table->unsignedInteger('line')->nullable();
            $table->text('trace')->nullable();
            $table->unsignedInteger('occurrences')->default(0);
            $table->dateTime('first_seen_at');
            $table->dateTime('last_seen_at')->index();
            $table->dateTime('resolved_at')->nullable()->index();
            $table->dateTime('notified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('error_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('error_report_id')->constrained()->cascadeOnDelete();
            // The reference the error page shows to the person (the request id, also in the log)
            $table->string('reference', 36)->index();
            $table->string('method', 10)->nullable();
            $table->text('url')->nullable();
            $table->string('route')->nullable();
            // Copied, without a foreign key: the users are deleted every course
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->dateTime('occurred_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_occurrences');
        Schema::dropIfExists('error_reports');
    }
};
