<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Enlaza cada convocatoria ya celebrada con la única reunión de su comité en el mismo día; las dudosas se quedan sin enlace
    public function up(): void
    {
        // por id y no por páginas: cada convocatoria enlazada sale del filtro mientras se recorre
        DB::table('meeting_requests')->whereNull('meeting_id')->whereNotNull('committee_id')->whereNotNull('datetime')
            ->eachById(function (object $request): void {
                $ids = DB::table('meetings')->where('committee_id', $request->committee_id)
                    ->whereDate('datetime', substr((string) $request->datetime, 0, 10))->pluck('id');
                if ($ids->count() === 1) {
                    DB::table('meeting_requests')->where('id', $request->id)->update(['meeting_id' => $ids->first()]);
                }
            });
    }

    // antes de esta fase nadie escribía la columna
    public function down(): void
    {
        DB::table('meeting_requests')->update(['meeting_id' => null]);
    }
};
