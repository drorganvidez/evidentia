<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class EvidentiaUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evidentia:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Evidentia App';

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
     * @return int
     */
    public function handle()
    {
        Artisan::call('down');

        exec("git pull");

        exec("composer update");

        Artisan::call("optimize:clear");

        $this->info("Evidentia has been updated successfully!");

        Artisan::call('up');
    }
}
