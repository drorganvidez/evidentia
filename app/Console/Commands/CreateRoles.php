<?php

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;

class CreateRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea los roles iniciales en la base de datos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $roles = [
            ['id' => 1, 'rol' => 'LECTURE', 'slug' => 'Profesor'],
            ['id' => 2, 'rol' => 'PRESIDENT', 'slug' => 'Presidente'],
            ['id' => 3, 'rol' => 'REGISTER_COORDINATOR', 'slug' => 'Coordinador de registro'],
            ['id' => 4, 'rol' => 'COORDINATOR', 'slug' => 'Coordinador'],
            ['id' => 5, 'rol' => 'SECRETARY', 'slug' => 'Secretario'],
            ['id' => 6, 'rol' => 'STUDENT', 'slug' => 'Estudiante'],
        ];

        if (Role::count() > 0 && ! $this->confirm('Ya existen roles. ¿Deseas sobrescribirlos? Esta acción los borrará todos.')) {
            $this->info('❌ Operación cancelada.');

            return Command::SUCCESS;
        }

        Role::query()->delete();
        Role::insert($roles);

        $this->info('✅ Roles creados correctamente.');

        return Command::SUCCESS;
    }
}
