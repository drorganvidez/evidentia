<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class evidentiastart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evidentia:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Evidentia App';

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
        DB::connection()->getPdo()->exec("CREATE DATABASE IF NOT EXISTS `homestead`");

        DB::connection()->getPdo()->exec("ALTER SCHEMA `homestead`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");

        Artisan::call('migrate:fresh',
            [
                '--seed' => 'DatabaseSeeder'
            ]);

        $this->info("Evidentia has started successfully. Enjoy!");
    }
}
