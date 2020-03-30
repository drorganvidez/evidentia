<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'David',
            'surname' => 'Romero',
            'email' => 'admin@admin.com',
            'username' => 'admin@admin.com',
            'password' => Hash::make('admin'),
        ]);
    }
}
