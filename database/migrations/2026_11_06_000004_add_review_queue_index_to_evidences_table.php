<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // La cola de revisión de un comité, que se cuenta en cada petición del coordinador
    public function up(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            $table->index(['committee_id', 'status', 'last'], 'evidences_review_queue_index');
        });
    }

    public function down(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            // MySQL puede haber quitado el índice de la clave ajena de committee_id al crear este; necesita otro antes de borrarlo
            $mysql = in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true);
            if ($mysql && ! Schema::hasIndex('evidences', ['committee_id'])) {
                $table->index('committee_id');
            }
            $table->dropIndex('evidences_review_queue_index');
        });
    }
};
