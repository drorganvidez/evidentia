<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
