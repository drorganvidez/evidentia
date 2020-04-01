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
            'rol' => 'LECTURE'
        ]);

        DB::table('roles')->insert([
            'id' => 2,
            'rol' => 'PRESIDENT'
        ]);

        DB::table('roles')->insert([
            'id' => 3,
            'rol' => 'REGISTER_COORDINATOR'
        ]);

        DB::table('roles')->insert([
            'id' => 4,
            'rol' => 'COORDINATOR'
        ]);

        DB::table('roles')->insert([
            'id' => 5,
            'rol' => 'SECRETARY'
        ]);

        DB::table('roles')->insert([
            'id' => 6,
            'rol' => 'STUDENT'
        ]);

        /*
         *  USUARIOS
         */

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Alumno',
            'surname' => 'Primero',
            'email' => 'alumno1@alumno1.com',
            'username' => 'alumno1@alumno1.com',
            'password' => Hash::make('alumno1'),
        ]);

        DB::table('users')->insert([
            'id' => 2,
            'name' => 'Alumno',
            'surname' => 'Segundo',
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
            'name' => 'Coordinador',
            'surname' => 'Coocoordina',
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
         *  COMITÉS
         */

        DB::table('comittees')->insert(['group' => 1, 'comittee' => 'Presidencia' ]);
        DB::table('comittees')->insert(['group' => 2, 'comittee' => 'Secretaría' ]);
        DB::table('comittees')->insert(['group' => 3, 'comittee' => 'Programa' ]);
        DB::table('comittees')->insert(['group' => 4, 'comittee' => 'Igualdad' ]);
        DB::table('comittees')->insert(['group' => 5, 'comittee' => 'Sostenibilidad' ]);
        DB::table('comittees')->insert(['group' => 6, 'comittee' => 'Finanzas' ]);
        DB::table('comittees')->insert(['group' => 7, 'comittee' => 'Logística', 'subcomitte' => 'Sede' ]);
        DB::table('comittees')->insert(['group' => 7, 'comittee' => 'Logística', 'subcomitte' => 'Registro' ]);
        DB::table('comittees')->insert(['group' => 7, 'comittee' => 'Logística', 'subcomitte' => 'Medios Audiovisuales' ]);
        DB::table('comittees')->insert(['group' => 7, 'comittee' => 'Logística', 'subcomitte' => 'Eventos Sociales' ]);
        DB::table('comittees')->insert(['group' => 8, 'comittee' => 'Comunicación', 'subcomitte' => 'Web' ]);
        DB::table('comittees')->insert(['group' => 8, 'comittee' => 'Comunicación', 'subcomitte' => 'Publicidad' ]);
        DB::table('comittees')->insert(['group' => 8, 'comittee' => 'Comunicación', 'subcomitte' => 'Redes Sociales' ]);
        DB::table('comittees')->insert(['group' => 8, 'comittee' => 'Comunicación', 'subcomitte' => 'Diseño' ]);

        DB::table('configuration')->insert([
            'secret' => Str::random(10),

        ]);

    }
}
