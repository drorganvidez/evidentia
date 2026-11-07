<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Name and edition of the event the platform runs for, shown on the access page and in every PDF. The course already
 * running keeps the name it had printed so far.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuration', function (Blueprint $table) {
            $table->string('event_name', 120)->nullable()->after('id');
            $table->string('event_edition', 20)->nullable()->after('event_name');
        });

        DB::table('configuration')->where('id', 1)->update(['event_name' => 'Jornadas InnoSoft Days', 'event_edition' => '2026']);
    }

    public function down(): void
    {
        Schema::table('configuration', function (Blueprint $table) {
            $table->dropColumn(['event_name', 'event_edition']);
        });
    }
};
