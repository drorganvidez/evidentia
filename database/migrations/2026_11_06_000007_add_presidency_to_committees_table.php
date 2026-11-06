<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The committee of the presidency, whose evidences the presidents review as its coordinators, and who also review the
 * evidences of every coordinator. Idempotent: it marks the committee called «Presidencia» that the courses already
 * have and only creates one when there is none; no other row changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('committees', 'presidency')) {
            Schema::table('committees', function (Blueprint $table) {
                $table->boolean('presidency')->default(false)->after('icon');
            });
        }

        if (DB::table('committees')->where('presidency', true)->exists()) {
            return;
        }

        $existing = DB::table('committees')->where('name', 'Presidencia')->orderBy('id')->value('id');
        if ($existing !== null) {
            DB::table('committees')->where('id', $existing)->update(['presidency' => true]);

            return;
        }

        DB::table('committees')->insert([
            'name' => 'Presidencia', 'icon' => 'crown', 'presidency' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    // the committee stays: evidences may already point to it
    public function down(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            $table->dropColumn('presidency');
        });
    }
};
