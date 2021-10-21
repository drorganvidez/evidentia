<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CreateDefaultInstance extends Command
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

        exec("php artisan config:cache");
        exec("php artisan config:clear");
        exec("php artisan cache:clear");

        try{

            $this->line('Dropping default instance if exists');
            DB::connection()->getPdo()->exec("DROP DATABASE IF EXISTS `base21`;");

            $this->line('Creating default instance');
            DB::connection()->getPdo()->exec("CREATE DATABASE `base21`");
            $this->line('Creating default instance ... [OK]');

            $this->line('Setting character set to UTF8MB4');
            DB::connection()->getPdo()->exec("ALTER SCHEMA `base21`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");
            $this->line('Setting character set to UTF8MB4 ... [OK]');

            $this->line('Creating migrations table');
            DB::connection()->getPdo()->exec("CREATE TABLE `base21`.`migrations` (`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,`migration` VARCHAR(255) NOT NULL,`batch` INT(11) NOT NULL, PRIMARY KEY (`id`));");
            $this->line('Creating migrations table ... [OK]');

            $this->line('Registering instance');
            Artisan::call('db:seed',
                [
                    '--class' => 'InstancesTableSeeder'
                ]);
            $this->line('Registering instance ... [OK]');

            // Migración y seeder
            $this->line('Migrating');
            exec('php artisan migrate --path database/migrations/develop');
            exec('php artisan db:seed --class=DevelopSeeder');
            $this->line('Migrating ... [OK]');

            $this->info('Instance created successfully.');
        }catch (\Exception $e){
            $this->error($e.' There seems to be a problem creating the instance. Check that it has not been previously instantiated.');
            $this->comment("Tip: use 'php artisan evidentia:reloadinstance' if you want to reload instance settings and database.");
        }

    }
}
