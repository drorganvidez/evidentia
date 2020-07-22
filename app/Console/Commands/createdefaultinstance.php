<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class createdefaultinstance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evidentia:createinstance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a default instance';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        DB::connection()->getPdo()->exec("CREATE DATABASE `base20`");

        DB::connection()->getPdo()->exec("ALTER SCHEMA `base20`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");

        DB::connection()->getPdo()->exec("CREATE TABLE `base20`.`migrations` (`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,`migration` VARCHAR(255) NOT NULL,`batch` INT(11) NOT NULL, PRIMARY KEY (`id`));");

        Artisan::call('db:seed',
            [
                '--class' => 'InstancesTableSeeder'
            ]);

        Artisan::call('migrate',
            [
                '--path' => 'database/migrations/develop'
            ]);

        Artisan::call('db:seed',
            [
                '--class' => 'DevelopSeeder'
            ]);

        $this->info('Instance created successfully.');
    }
}
