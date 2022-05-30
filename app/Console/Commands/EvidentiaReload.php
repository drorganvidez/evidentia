<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class EvidentiaReload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evidentia:reload {type}';

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

        $type = $this->argument('type');

        if($type != "docker" && $type != "vagrant"){
            throw new \InvalidArgumentException("Missing type
            \nHere you are valid examples:
            \nphp artisan evidentia:reload docker\nphp artisan evidentia:reload vagrant");
        }

        if($type == "docker"){
            exec("php artisan evidentia:start docker");
        }

        if($type == "vagrant"){
            exec("php artisan evidentia:start vagrant");
        }

        exec("php artisan evidentia:instance");

        $this->info('Evidentia reloaded successfully.');
    }
}
