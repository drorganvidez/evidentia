<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional caps on the hours that count from accepted evidences and from attended events; null counts them all.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuration', function (Blueprint $table) {
            $table->decimal('max_evidences_hours', 6, 2)->nullable()->after('attendee_timestamp');
            $table->decimal('max_attendees_hours', 6, 2)->nullable()->after('max_evidences_hours');
        });
    }

    public function down(): void
    {
        Schema::table('configuration', function (Blueprint $table) {
            $table->dropColumn(['max_evidences_hours', 'max_attendees_hours']);
        });
    }
};
