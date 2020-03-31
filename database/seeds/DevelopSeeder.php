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

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Alumno',
            'surname' => 'Primero',
            'email' => 'user1@user1.com',
            'username' => 'user1@user1.com',
            'password' => Hash::make('user1'),
        ]);

        DB::table('users')->insert([
            'id' => 2,
            'name' => 'Alumno',
            'surname' => 'Segundo',
            'email' => 'user2@user2.com',
            'username' => 'user2@user2.com',
            'password' => Hash::make('user2'),
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
            'name' => 'Coordinador de registro',
            'surname' => 'Cocoacalo',
            'email' => 'coordinadorregistro1@coordinadorregistro1.com',
            'username' => 'coordinadorregistro1@coordinadorregistro1.com',
            'password' => Hash::make('coordinadorregistro1'),
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
            'name' => 'Presidente',
            'surname' => 'Pérez',
            'email' => 'presidente1@presidente1.com',
            'username' => 'presidente1@presidente1.com',
            'password' => Hash::make('presidente1'),
        ]);

        DB::table('users')->insert([
            'id' => 7,
            'name' => 'Profesor',
            'surname' => 'Popitas',
            'email' => 'profesor1@profesor1.com',
            'username' => 'profesor1@profesor1.com',
            'password' => Hash::make('profesor1'),
        ]);

        DB::table('role_user')->insert([
            'role_id' => 1,
            'user_id' => 6
        ]);

        DB::table('role_user')->insert([
            'role_id' => 2,
            'user_id' => 7
        ]);

        DB::table('role_user')->insert([
            'role_id' => 3,
            'user_id' => 4
        ]);

        DB::table('role_user')->insert([
            'role_id' => 4,
            'user_id' => 5
        ]);

        DB::table('role_user')->insert([
            'role_id' => 5,
            'user_id' => 3
        ]);

        DB::table('role_user')->insert([
            'role_id' => 5,
            'user_id' => 1
        ]);

        DB::table('role_user')->insert([
            'role_id' => 5,
            'user_id' => 2
        ]);

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
