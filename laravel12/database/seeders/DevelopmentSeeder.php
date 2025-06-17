<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DevelopmentSeeder extends Seeder
{
    public function run()
    {
        // ROLES
        DB::table('roles')->insert([
            ['id' => 1, 'rol' => 'LECTURE', 'slug' => 'Profesor'],
            ['id' => 2, 'rol' => 'PRESIDENT', 'slug' => 'Presidente'],
            ['id' => 3, 'rol' => 'REGISTER_COORDINATOR', 'slug' => 'Coordinador de registro'],
            ['id' => 4, 'rol' => 'COORDINATOR', 'slug' => 'Coordinador'],
            ['id' => 5, 'rol' => 'SECRETARY', 'slug' => 'Secretario'],
            ['id' => 6, 'rol' => 'STUDENT', 'slug' => 'Estudiante'],
        ]);

        // USUARIOS
        DB::table('users')->insert([
            'id' => 1,
            'name' => env('LECTURE_NEW_INSTANCE_NAME', 'Profesor'),
            'surname' => env('LECTURE_NEW_INSTANCE_SURNAME', 'Profesor'),
            'email' => env('LECTURE_NEW_INSTANCE_EMAIL', 'profesor1@profesor1.com'),
            'username' => env('LECTURE_NEW_INSTANCE_USERNAME', 'profesor1'),
            'password' => Hash::make(env('LECTURE_NEW_INSTANCE_PASSWORD', 'profesor1')),
        ]);

        // RELACIÓN DE USUARIOS Y ROLES
        DB::table('role_user')->insert([
            'role_id' => 1,
            'user_id' => 1,
        ]);

        // COMITÉS
        DB::table('committees')->insert([
            ['id' => 1, 'name' => 'Presidencia', 'icon' => '<i class="fas fa-user-tie"></i>'],
            ['id' => 2, 'name' => 'Secretaría', 'icon' => '<i class="fas fa-file-signature"></i>'],
            ['id' => 3, 'name' => 'Programa', 'icon' => '<i class="fas fa-list-ol"></i>'],
            ['id' => 4, 'name' => 'Igualdad', 'icon' => '<i class="fas fa-people-carry"></i>'],
            ['id' => 5, 'name' => 'Sostenibilidad', 'icon' => '<i class="fas fa-piggy-bank"></i>'],
            ['id' => 6, 'name' => 'Finanzas', 'icon' => '<i class="fas fa-euro-sign"></i>'],
            ['id' => 7, 'name' => 'Logística', 'icon' => '<i class="fas fa-warehouse"></i>'],
            ['id' => 8, 'name' => 'Comunicación', 'icon' => '<i class="fab fa-twitter"></i>'],
        ]);

        // CONFIGURACIÓN
        DB::table('configuration')->insert([
            'secret' => Str::random(10),
        ]);
    }
}