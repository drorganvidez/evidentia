<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EvidentiaDeploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evidentia:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Evidentia App in server';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('Setting environment');
        exec('cp .env.deploy .env');
        $this->line('Setting environment ... [OK]');

        $this->line('Dropping main database');
        DB::connection()->getPdo()->exec("DROP DATABASE IF EXISTS `evidentia`;");
        $this->line('Dropping main database ... [OK]');

        // Creamos la base de datos principal
        $this->line('Creating main database');
        DB::connection()->getPdo()->exec("CREATE DATABASE IF NOT EXISTS `evidentia`");
        $this->line('Creating main database ... [OK]');

        $this->line('Setting character set to UTF8MB4');
        DB::connection()->getPdo()->exec("ALTER SCHEMA `evidentia`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");
        $this->line('Setting character set to UTF8MB4 ... [OK]');

        $this->line('Migrating');
        exec("php artisan migrate");
        exec("php artisan db:seed");
        $this->line('Migrating ... [OK]');

        $this->info("Evidentia has been deployed successfully. Enjoy!");

        return 0;
    }
}
