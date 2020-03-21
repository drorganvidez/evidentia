<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

class UsersBase20TableSeeder extends Seeder
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
            'name' => 'Usuario Primero',
            'email' => 'user1@user1.com',
            'username' => 'user1@user1.com',
            'password' => Hash::make('user1'),
        ]);

        DB::table('users')->insert([
            'name' => 'Usuario Segundo',
            'email' => 'user2@user2.com',
            'username' => 'user2@user2.com',
            'password' => Hash::make('user2'),
        ]);
    }
}
