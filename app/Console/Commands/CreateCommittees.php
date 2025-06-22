<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateCommittees extends Command
{
    protected $signature = 'create:committees';
    protected $description = 'Inserta los comités predefinidos si no existen o los actualiza';

    public function handle()
    {
        $committees = [
            ['id' => 1, 'name' => 'Presidencia', 'icon' => '<i class="fas fa-user-tie"></i>'],
            ['id' => 2, 'name' => 'Secretaría', 'icon' => '<i class="fas fa-file-signature"></i>'],
            ['id' => 3, 'name' => 'Programa', 'icon' => '<i class="fas fa-list-ol"></i>'],
            ['id' => 4, 'name' => 'Igualdad', 'icon' => '<i class="fas fa-people-carry"></i>'],
            ['id' => 5, 'name' => 'Sostenibilidad', 'icon' => '<i class="fas fa-piggy-bank"></i>'],
            ['id' => 6, 'name' => 'Finanzas', 'icon' => '<i class="fas fa-euro-sign"></i>'],
            ['id' => 7, 'name' => 'Logística', 'icon' => '<i class="fas fa-warehouse"></i>'],
            ['id' => 8, 'name' => 'Comunicación', 'icon' => '<i class="fab fa-twitter"></i>'],
            ['id' => 9, 'name' => 'I+D+I', 'icon' => '<i class="fas fa-terminal"></i>'],
        ];

        DB::table('committees')->upsert($committees, ['id'], ['name', 'icon']);

        $this->info('✅ Comités creados o actualizados correctamente');
        return Command::SUCCESS;
    }
}
