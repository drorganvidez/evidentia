<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class DevelopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Config::set('database.connections.instance.host', 'localhost');
        Config::set('database.connections.instance.port', '33060');
        Config::set('database.connections.instance.username', 'homestead');
        Config::set('database.connections.instance.password', 'secret');
        Config::set('database.connections.instance.database', 'base20');
        Config::set('database.default', 'instance');

        Artisan::call('config:clear');

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

        /*
         *  USUARIOS
         */

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'David',
            'surname' => 'Romero Organvídez',
            'email' => 'alumno1@alumno1.com',
            'username' => 'alumno1@alumno1.com',
            'password' => Hash::make('alumno1'),
        ]);

        DB::table('users')->insert([
            'id' => 2,
            'name' => 'Ron',
            'surname' => 'Weasley',
            'email' => 'alumno2@alumno2.com',
            'username' => 'alumno2@alumno2.com',
            'password' => Hash::make('alumno2'),
        ]);

        DB::table('users')->insert([
            'id' => 3,
            'name' => 'Secretario',
            'surname' => 'Segismundo',
            'email' => 'secretario1@secretario1.com',
            'username' => 'secretario1@secretario1.com',
            'password' => Hash::make('secretario1'),
        ]);

        DB::table('users')->insert([
            'id' => 4,
            'name' => 'Secretario',
            'surname' => 'Segismundo',
            'email' => 'secretario2@secretario2.com',
            'username' => 'secretario2@secretario2.com',
            'password' => Hash::make('secretario2'),
        ]);

        DB::table('users')->insert([
            'id' => 5,
            'name' => 'Hermione',
            'surname' => 'Jane Granger',
            'email' => 'coordinador1@coordinador1.com',
            'username' => 'coordinador1@coordinador1.com',
            'password' => Hash::make('coordinador1'),
        ]);

        DB::table('users')->insert([
            'id' => 6,
            'name' => 'Coordinador',
            'surname' => 'Coocoordina',
            'email' => 'coordinador2@coordinador2.com',
            'username' => 'coordinador2@coordinador2.com',
            'password' => Hash::make('coordinador2'),
        ]);

        DB::table('users')->insert([
            'id' => 7,
            'name' => 'Coordinador de registro',
            'surname' => 'Cocoacalo',
            'email' => 'coordinadorregistro1@coordinadorregistro1.com',
            'username' => 'coordinadorregistro1@coordinadorregistro1.com',
            'password' => Hash::make('coordinadorregistro1'),
        ]);

        DB::table('users')->insert([
            'id' => 8,
            'name' => 'Coordinador de registro',
            'surname' => 'Cocoacalo',
            'email' => 'coordinadorregistro2@coordinadorregistro2.com',
            'username' => 'coordinadorregistro2@coordinadorregistro2.com',
            'password' => Hash::make('coordinadorregistro2'),
        ]);

        DB::table('users')->insert([
            'id' => 9,
            'name' => 'Presidente',
            'surname' => 'Pérez',
            'email' => 'presidente1@presidente1.com',
            'username' => 'presidente1@presidente1.com',
            'password' => Hash::make('presidente1'),
        ]);

        DB::table('users')->insert([
            'id' => 10,
            'name' => 'Presidente',
            'surname' => 'Pérez',
            'email' => 'presidente2@presidente2.com',
            'username' => 'presidente2@presidente2.com',
            'password' => Hash::make('presidente2'),
        ]);

        DB::table('users')->insert([
            'id' => 11,
            'name' => 'Profesor',
            'surname' => 'Popitas',
            'email' => 'profesor1@profesor1.com',
            'username' => 'profesor1@profesor1.com',
            'password' => Hash::make('profesor1'),
        ]);

        DB::table('users')->insert([
            'id' => 12,
            'name' => 'Profesor',
            'surname' => 'Popitas',
            'email' => 'profesor2@profesor2com',
            'username' => 'profesor2@profesor2.com',
            'password' => Hash::make('profesor2'),
        ]);

        /*
         *  RELACIÓN DE USUARIOS Y ROLES
         */

        DB::table('role_user')->insert([
            'role_id' => 6,
            'user_id' => 1
        ]);

        DB::table('role_user')->insert([
            'role_id' => 6,
            'user_id' => 2
        ]);

        DB::table('role_user')->insert([
            'role_id' => 6,
            'user_id' => 3
        ]);

        DB::table('role_user')->insert([
            'role_id' => 6,
            'user_id' => 4
        ]);

        DB::table('role_user')->insert([
            'role_id' => 6,
            'user_id' => 5
        ]);

        DB::table('role_user')->insert([
            'role_id' => 6,
            'user_id' => 6
        ]);

        DB::table('role_user')->insert([
            'role_id' => 6,
            'user_id' => 7
        ]);

        DB::table('role_user')->insert([
            'role_id' => 6,
            'user_id' => 8
        ]);

        DB::table('role_user')->insert([
            'role_id' => 6,
            'user_id' => 9
        ]);

        DB::table('role_user')->insert([
            'role_id' => 6,
            'user_id' => 10
        ]);

        DB::table('role_user')->insert([
            'role_id' => 5,
            'user_id' => 3
        ]);

        DB::table('role_user')->insert([
            'role_id' => 5,
            'user_id' => 4
        ]);

        DB::table('role_user')->insert([
            'role_id' => 4,
            'user_id' => 5
        ]);

        DB::table('role_user')->insert([
            'role_id' => 4,
            'user_id' => 6
        ]);

        DB::table('role_user')->insert([
            'role_id' => 3,
            'user_id' => 7
        ]);

        DB::table('role_user')->insert([
            'role_id' => 3,
            'user_id' => 8
        ]);

        DB::table('role_user')->insert([
            'role_id' => 2,
            'user_id' => 9
        ]);

        DB::table('role_user')->insert([
            'role_id' => 2,
            'user_id' => 10
        ]);

        DB::table('role_user')->insert([
            'role_id' => 1,
            'user_id' => 11
        ]);

        DB::table('role_user')->insert([
            'role_id' => 1,
            'user_id' => 12
        ]);

        /*
         *  COORDINADORES Y SECRETARIOS
         */

        DB::table('coordinators')->insert([
        'user_id' => 5,
        'comittee_id' => 5
    ]);

        DB::table('coordinators')->insert([
            'user_id' => 6,
            'comittee_id' => 7
        ]);

        DB::table('secretaries')->insert([
            'user_id' => 3,
            'comittee_id' => 5
        ]);

        DB::table('secretaries')->insert([
            'user_id' => 4,
            'comittee_id' => 7
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
            'name' => 'Logística'
        ]);

        DB::table('comittees')->insert([
            'id' => 8,
            'name' => 'Comunicación'
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
