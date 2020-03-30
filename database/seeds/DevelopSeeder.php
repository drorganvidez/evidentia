<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

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

        DB::table('users')->insert([
            'name' => 'Usuario',
            'surname' => 'Primero',
            'email' => 'user1@user1.com',
            'username' => 'user1@user1.com',
            'password' => Hash::make('user1'),
        ]);

        DB::table('users')->insert([
            'name' => 'Usuario',
            'surname' => 'Segundo',
            'email' => 'user2@user2.com',
            'username' => 'user2@user2.com',
            'password' => Hash::make('user2'),
        ]);

        DB::table('comittees')->insert(['comittee' => 'Presidencia' ]);
        DB::table('comittees')->insert(['comittee' => 'Secretaría' ]);
        DB::table('comittees')->insert(['comittee' => 'Programa' ]);
        DB::table('comittees')->insert(['comittee' => 'Igualdad' ]);
        DB::table('comittees')->insert(['comittee' => 'Sostenibilidad' ]);
        DB::table('comittees')->insert(['comittee' => 'Finanzas' ]);
        DB::table('comittees')->insert(['comittee' => 'Logística', 'subcomitte' => 'Sede' ]);
        DB::table('comittees')->insert(['comittee' => 'Logística', 'subcomitte' => 'Registro' ]);
        DB::table('comittees')->insert(['comittee' => 'Logística', 'subcomitte' => 'Medios Audiovisuales' ]);
        DB::table('comittees')->insert(['comittee' => 'Logística', 'subcomitte' => 'Eventos Sociales' ]);
        DB::table('comittees')->insert(['comittee' => 'Comunicación', 'subcomitte' => 'Web' ]);
        DB::table('comittees')->insert(['comittee' => 'Comunicación', 'subcomitte' => 'Publicidad' ]);
        DB::table('comittees')->insert(['comittee' => 'Comunicación', 'subcomitte' => 'Redes Sociales' ]);
        DB::table('comittees')->insert(['comittee' => 'Comunicación', 'subcomitte' => 'Diseño' ]);

    }
}
