<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /*
         *  ROLES
         */

        DB::table('roles')->insert([
            'id' => 1,
            'rol' => 'LECTURE',
            'slug' => 'Profesor'
        ]);

        DB::table('roles')->insert([
            'id' => 2,
            'rol' => 'PRESIDENT',
            'slug' => 'Presidente'
        ]);

        DB::table('roles')->insert([
            'id' => 3,
            'rol' => 'REGISTER_COORDINATOR',
            'slug' => 'Coordinador de registro'
        ]);

        DB::table('roles')->insert([
            'id' => 4,
            'rol' => 'COORDINATOR',
            'slug' => 'Coordinador'
        ]);

        DB::table('roles')->insert([
            'id' => 5,
            'rol' => 'SECRETARY',
            'slug' => 'Secretario'
        ]);

        DB::table('roles')->insert([
            'id' => 6,
            'rol' => 'STUDENT',
            'slug' => 'Estudiante'
        ]);

        DB::table('roles')->insert([
            'id' => 7,
            'rol' => 'COLLABORATOR',
            'slug' => 'Colaborador'
        ]);

        /*
         *  USUARIOS
         */

        DB::table('users')->insert([
            'id' => 1,
            'name' => env('LECTURE_NEW_INSTANCE_NAME','Profesor'),
            'surname' => env('LECTURE_NEW_INSTANCE_SURNAME','Profesor'),
            'email' => env('LECTURE_NEW_INSTANCE_EMAIL','profesor1@profesor1.com'),
            'username' => env('LECTURE_NEW_INSTANCE_USERNAME','profesor1'),
            'password' => Hash::make(env('LECTURE_NEW_INSTANCE_PASSWORD','profesor1')),
        ]);

        /*
         *  RELACIÓN DE USUARIOS Y ROLES
         */

        DB::table('role_user')->insert([
            'role_id' => 1,
            'user_id' => 1
        ]);

        /*
         *  COMITÉS & SUBCOMITÉS
         */

        DB::table('comittees')->insert([
            'id' => 1,
            'name' => 'Presidencia',
            'icon' => '<i class="fas fa-user-tie"></i>'
        ]);

        DB::table('comittees')->insert([
            'id' => 2,
            'name' => 'Secretaría',
            'icon' => '<i class="fas fa-file-signature"></i>'
        ]);

        DB::table('comittees')->insert([
            'id' => 3,
            'name' => 'Programa',
            'icon' => '<i class="fas fa-list-ol"></i>'
        ]);

        DB::table('comittees')->insert([
            'id' => 4,
            'name' => 'Igualdad',
            'icon' => '<i class="fas fa-people-carry"></i>'
        ]);

        DB::table('comittees')->insert([
            'id' => 5,
            'name' => 'Sostenibilidad',
            'icon' => '<i class="fas fa-piggy-bank"></i>'
        ]);

        DB::table('comittees')->insert([
            'id' => 6,
            'name' => 'Finanzas',
            'icon' => '<i class="fas fa-euro-sign"></i>'
        ]);

        DB::table('comittees')->insert([
            'id' => 7,
            'name' => 'Logística',
            'icon' => '<i class="fas fa-warehouse"></i>'
        ]);

        DB::table('comittees')->insert([
            'id' => 8,
            'name' => 'Comunicación',
            'icon' => '<i class="fab fa-twitter"></i>'
        ]);

        DB::table('subcomittees')->insert([
            'id' => 1,
            'comittee_id' => 7,
            'name' => 'Sede',
            'icon' => '<i class="fas fa-map-pin"></i>'
        ]);

        DB::table('subcomittees')->insert([
            'id' => 2,
            'comittee_id' => 7,
            'name' => 'Registro',
            'icon' => '<i class="fas fa-user-check"></i>'
        ]);

        DB::table('subcomittees')->insert([
            'id' => 3,
            'comittee_id' => 7,
            'name' => 'Medios Audiovisuales',
            'icon' => '<i class="fas fa-film"></i>'
        ]);

        DB::table('subcomittees')->insert([
            'id' => 4,
            'comittee_id' => 7,
            'name' => 'Eventos Sociales',
            'icon' => '<i class="fas fa-users"></i>'
        ]);

        DB::table('subcomittees')->insert([
            'id' => 5,
            'comittee_id' => 8,
            'name' => 'Web',
            'icon' => '<i class="fas fa-laptop"></i>'
        ]);

        DB::table('subcomittees')->insert([
            'id' => 6,
            'comittee_id' => 8,
            'name' => 'Publicidad',
            'icon' => '<i class="fas fa-ad"></i>'
        ]);

        DB::table('subcomittees')->insert([
            'id' => 7,
            'comittee_id' => 8,
            'name' => 'Redes Sociales',
            'icon' => '<i class="fas fa-hashtag"></i>'
        ]);

        DB::table('subcomittees')->insert([
            'id' => 8,
            'comittee_id' => 8,
            'name' => 'Diseño',
            'icon' => '<i class="fab fa-sketch"></i>'
        ]);

        /*
         *  CONFIGURACIÓN
         */

        DB::table('configuration')->insert([
            'secret' => Str::random(10),
        ]);

    }
}
