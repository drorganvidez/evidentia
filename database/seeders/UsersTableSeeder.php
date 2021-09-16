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
            'name' => env('ADMIN_NAME','David'),
            'surname' => env('ADMIN_SURNAME','Romero'),
            'email' => env('ADMIN_EMAIL','admin@admin.com'),
            'username' => env('ADMIN_USERNAME','admin@admin.com'),
            'password' => Hash::make(env('ADMIN_PASSWORD','admin')),
        ]);
    }
}
