<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class EvidentiaMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evidentia:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        $this->line('Migrating');
        Artisan::call('migrate:refresh', ['--path' => 'database/migrations/develop']);
        $this->line('Migrating ... [OK]');

        $this->line('Seeding');
        Artisan::call('db:seed',['--class' => 'DevelopSeeder']);
        $this->line('Seeding ... [OK]');

        $this->info('Evidentia migrated successfully.');

        return 0;
    }
}
