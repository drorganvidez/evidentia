<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateLectureUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:professor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea un usuario con rol PROFESOR pidiendo email y contraseña por consola';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->ask('Introduce el email del profesor');
        $username = $this->anticipate('Introduce un nombre de usuario', [explode('@', $email)[0]]);
        $name = $this->ask('Nombre real (opcional)', 'Profesor');
        $surname = $this->ask('Apellido (opcional)', 'Ejemplo');

        if (User::where('email', $email)->exists()) {
            $this->error('Ya existe un usuario con ese email.');

            return Command::FAILURE;
        }

        $password = $this->secret('Introduce la contraseña');
        $confirm = $this->secret('Confirma la contraseña');

        if ($password !== $confirm) {
            $this->error('Las contraseñas no coinciden.');

            return Command::FAILURE;
        }

        $this->info('Se va a crear el siguiente usuario:');
        $this->line("Email: $email");
        $this->line("Usuario: $username");
        $this->line("Nombre: $name $surname");

        if (! $this->confirm('¿Deseas continuar?')) {
            $this->info('Operación cancelada.');

            return Command::SUCCESS;
        }

        $user = User::create([
            'email' => $email,
            'username' => $username,
            'name' => $name,
            'surname' => $surname,
            'password' => Hash::make($password),
        ]);

        // Asignar rol LECTURE (ID 1)
        DB::table('role_user')->updateOrInsert([
            'user_id' => $user->id,
            'role_id' => 1, // LECTURE
        ]);

        $this->info("✅ Profesor creado correctamente con ID {$user->id}");

        return Command::SUCCESS;
    }
}