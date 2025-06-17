<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class DevelopmentSeeder extends Seeder
{
    public function run()
    {
        // ROLES
        DB::table('roles')->upsert([
            ['id' => 1, 'rol' => 'LECTURE', 'slug' => 'Profesor'],
            ['id' => 2, 'rol' => 'PRESIDENT', 'slug' => 'Presidente'],
            ['id' => 3, 'rol' => 'REGISTER_COORDINATOR', 'slug' => 'Coordinador de registro'],
            ['id' => 4, 'rol' => 'COORDINATOR', 'slug' => 'Coordinador'],
            ['id' => 5, 'rol' => 'SECRETARY', 'slug' => 'Secretario'],
            ['id' => 6, 'rol' => 'STUDENT', 'slug' => 'Estudiante'],
        ], ['id']);

        // USUARIOS USANDO FACTORY (con datos controlados)
        $usersData = [
            [
                'name' => env('LECTURE_NEW_INSTANCE_NAME', 'Profesor'),
                'surname' => env('LECTURE_NEW_INSTANCE_SURNAME', 'Profesor'),
                'email' => env('LECTURE_NEW_INSTANCE_EMAIL', 'profesor1@profesor1.com'),
                'username' => env('LECTURE_NEW_INSTANCE_USERNAME', 'profesor1'),
                'password' => Hash::make(env('LECTURE_NEW_INSTANCE_PASSWORD', 'profesor1')),
            ],
            [
                'name' => 'Juan',
                'surname' => 'Pérez',
                'email' => 'user1@example.com',
                'username' => 'user1',
                'password' => Hash::make('user1'),
            ],
            [
                'name' => 'María',
                'surname' => 'Gómez',
                'email' => 'user2@example.com',
                'username' => 'user2',
                'password' => Hash::make('user2'),
            ],
            [
                'name' => 'Luis',
                'surname' => 'Fernández',
                'email' => 'user3@example.com',
                'username' => 'user3',
                'password' => Hash::make('user3'),
            ],
        ];

        foreach ($usersData as $userData) {
            User::factory()->create($userData);
        }

        // ASIGNACIÓN DE ROLES (evitar duplicados)
        $roleUsers = [
            ['role_id' => 1, 'user_id' => 1], // Profesor
            ['role_id' => 6, 'user_id' => 2], // Juan - Estudiante
            ['role_id' => 6, 'user_id' => 3], // María - Estudiante
            ['role_id' => 6, 'user_id' => 4], // Luis - Estudiante
        ];

        foreach ($roleUsers as $ru) {
            DB::table('role_user')->updateOrInsert($ru);
        }

        // COMITÉS
        DB::table('committees')->upsert([
            ['id' => 1, 'name' => 'Presidencia', 'icon' => '<i class="fas fa-user-tie"></i>'],
            ['id' => 2, 'name' => 'Secretaría', 'icon' => '<i class="fas fa-file-signature"></i>'],
            ['id' => 3, 'name' => 'Programa', 'icon' => '<i class="fas fa-list-ol"></i>'],
            ['id' => 4, 'name' => 'Igualdad', 'icon' => '<i class="fas fa-people-carry"></i>'],
            ['id' => 5, 'name' => 'Sostenibilidad', 'icon' => '<i class="fas fa-piggy-bank"></i>'],
            ['id' => 6, 'name' => 'Finanzas', 'icon' => '<i class="fas fa-euro-sign"></i>'],
            ['id' => 7, 'name' => 'Logística', 'icon' => '<i class="fas fa-warehouse"></i>'],
            ['id' => 8, 'name' => 'Comunicación', 'icon' => '<i class="fab fa-twitter"></i>'],
        ], ['id']);

        // CONFIGURACIÓN (solo si no existe)
        $oneYearLater = Carbon::now()->addYear();

        DB::table('configuration')->insert([
            'secret' => Str::random(10),
            'upload_evidences_timestamp' => $oneYearLater,
            'validate_evidences_timestamp' => $oneYearLater,
            'meetings_timestamp' => $oneYearLater,
            'bonus_timestamp' => $oneYearLater,
            'attendee_timestamp' => $oneYearLater,
            'events_uploaded_timestamp' => $oneYearLater,
            'attendees_uploaded_timestamp' => $oneYearLater,
        ]);
    }
}
