<?php

namespace App\Console\Commands;

use App\Models\Instance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\throwException;

class EvidentiaStart extends Command
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
     * @return int
     */
    public function handle()
    {

        $this->line('Setting environment');
        exec('cp .env.dev .env');
        $this->line('Setting environment ... [OK]');

        // Borramos la instancia por defecto
        $this->line('Dropping default instance');
        DB::connection()->getPdo()->exec("DROP DATABASE IF EXISTS `base21`;");
        $this->line('Dropping default instance ... [OK]');

        // Borramos las demás instancias
        $this->line('Dropping instances');
        try {
            $instances = Instance::all();
            foreach ($instances as $instance) {
                DB::statement("DROP DATABASE IF EXISTS `{$instance->database}`");
            }
            $this->line('Dropping instances ... [OK]');
        }catch(\Exception $e){
            $this->comment('No instances found');
        }

        // Borramos la base de datos principal
        $this->line('Dropping main database');
        DB::connection()->getPdo()->exec("DROP DATABASE IF EXISTS `evidentia`;");
        $this->line('Dropping main database ... [OK]');

        // Creamos la base de datos principal
        $this->line('Creating main database');
        DB::connection()->getPdo()->exec("CREATE DATABASE IF NOT EXISTS `evidentia`");
        $this->line('Creating main database ... [OK]');

        // Codificación UTF8 MB4
        $this->line('Setting character set to UTF8MB4');
        DB::connection()->getPdo()->exec("ALTER SCHEMA `evidentia`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");
        $this->line('Setting character set to UTF8MB4 ... [OK]');

        // Migraciones y seeders
        $this->line('Migrating');
        exec("php artisan migrate");
        exec("php artisan db:seed");
        $this->line('Migrating ... [OK]');

        $this->info("Evidentia has started successfully. Enjoy!");

        return 0;

    }

}
