<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class InstancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('instances')->insert([
            'name' => 'Curso 2020/21',
            'route' => '20',
            'host' => 'localhost',
            'port' => '33060',
            'username' => 'homestead',
            'password' => 'secret',
            'database' => 'base20',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'active' => true
        ]);

        DB::table('instances')->insert([
            'name' => 'Curso 2021/22',
            'route' => '21',
            'host' => 'localhost',
            'port' => '33060',
            'username' => 'homestead',
            'password' => 'secret',
            'database' => 'base21',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'active' => true
        ]);
    }
}
