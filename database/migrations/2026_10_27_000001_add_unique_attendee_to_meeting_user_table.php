<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Elimina asistentes repetidos en una misma reunión, conservando la primera fila
        $keep = DB::table('meeting_user')->selectRaw('MIN(id) as id')->groupBy('meeting_id', 'user_id')->pluck('id');
        DB::table('meeting_user')->whereNotIn('id', $keep)->delete();

        Schema::table('meeting_user', function (Blueprint $table) {
            $table->unique(['meeting_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('meeting_user', function (Blueprint $table) {
            // MySQL usa el índice único para la clave ajena de meeting_id, así que necesita otro antes de borrarlo
            if (! Schema::hasIndex('meeting_user', ['meeting_id'])) {
                $table->index('meeting_id');
            }
            $table->dropUnique(['meeting_id', 'user_id']);
        });
    }
};
