<?php

namespace App\Console\Commands;

use App\Models\Instance;
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

        $this->line('Optimizing');
        Artisan::call("optimize:clear");
        $this->line('Optimizing ... [OK]');

        $this->line('Setting environment file');
        exec("cat /dev/null > .env");
        exec('echo "APP_NAME=Laravel" >> .env');
        exec('echo "APP_ENV=localhost.local" >> .env');
        exec('echo "APP_KEY=" >> .env');
        exec('echo "APP_DEBUG=true" >> .env');
        exec('echo "APP_URL=http://localhost" >> .env');
        exec('echo "" >> .env');
        exec('echo "LOG_CHANNEL=stack" >> .env');
        exec('echo "" >> .env');
        exec('echo "DB_CONNECTION=mysql" >> .env');
        exec('echo "DB_HOST=localhost" >> .env');
        exec('echo "DB_PORT=33060" >> .env');
        exec('echo "DB_DATABASE=homestead" >> .env');
        exec('echo "DB_USERNAME=homestead" >> .env');
        exec('echo "DB_PASSWORD=secret" >> .env');
        exec('echo "DB_CHARSET=utf8" >> .env');
        exec('echo "DB_COLLATION=utf8_unicode_ci" >> .env');
        exec('echo "" >> .env');
        exec('echo "BROADCAST_DRIVER=log" >> .env');
        exec('echo "CACHE_DRIVER=file" >> .env');
        exec('echo "QUEUE_CONNECTION=sync" >> .env');
        exec('echo "SESSION_DRIVER=file" >> .env');
        exec('echo "SESSION_LIFETIME=120" >> .env');
        exec('echo "" >> .env');
        exec('echo "REDIS_HOST=127.0.0.1" >> .env');
        exec('echo "REDIS_PASSWORD=null" >> .env');
        exec('echo "REDIS_PORT=6379" >> .env');
        exec('echo "" >> .env');
        exec('echo "MAIL_MAILER=smtp" >> .env');
        exec('echo "MAIL_HOST=smtp.mailtrap.io" >> .env');
        exec('echo "MAIL_PORT=2525" >> .env');
        exec('echo "MAIL_USERNAME=null" >> .env');
        exec('echo "MAIL_PASSWORD=null" >> .env');
        exec('echo "MAIL_ENCRYPTION=null" >> .env');
        exec('echo "MAIL_FROM_ADDRESS=null" >> .env');
        exec('echo "MAIL_FROM_NAME=\"${APP_NAME}\"" >> .env');
        exec('echo "" >> .env');
        exec('echo "AWS_ACCESS_KEY_ID=" >> .env');
        exec('echo "AWS_SECRET_ACCESS_KEY=" >> .env');
        exec('echo "AWS_DEFAULT_REGION=us-east-1" >> .env');
        exec('echo "AWS_BUCKET=" >> .env');
        exec('echo "" >> .env');
        exec('echo "PUSHER_APP_ID=" >> .env');
        exec('echo "PUSHER_APP_KEY=" >> .env');
        exec('echo "PUSHER_APP_SECRET=" >> .env');
        exec('echo "PUSHER_APP_CLUSTER=mt1" >> .env');
        exec('echo "" >> .env');
        exec('echo "MIX_PUSHER_APP_KEY=\"${PUSHER_APP_KEY}\"" >> .env');
        exec('echo "MIX_PUSHER_APP_CLUSTER=\"${PUSHER_APP_CLUSTER}\"" >> .env');
        exec('echo "" >> .env');
        $this->line('Setting environment file ... [OK]');

        $this->line('Generating key');
        exec("php artisan config:cache");
        exec("php artisan key:generate");
        exec("php artisan config:cache");
        $this->line('Generating key ... [OK]');

        // Borramos la instancia por defecto
        $this->line('Dropping default instance');
        DB::connection()->getPdo()->exec("DROP DATABASE IF EXISTS `base20`;");
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
        DB::connection()->getPdo()->exec("DROP DATABASE IF EXISTS `homestead`;");
        $this->line('Dropping main database ... [OK]');

        // Creamos la base de datos principal
        $this->line('Creating main database');
        DB::connection()->getPdo()->exec("CREATE DATABASE IF NOT EXISTS `homestead`");
        $this->line('Creating main database ... [OK]');

        // Codificación UTF8 MB4
        $this->line('Setting character set to UTF8MB4');
        DB::connection()->getPdo()->exec("ALTER SCHEMA `homestead`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");
        $this->line('Setting character set to UTF8MB4 ... [OK]');

        // Migraciones y seeders
        $this->line('Migrating');
        exec("php artisan migrate");
        exec("php artisan db:seed");
        $this->line('Migrating ... [OK]');

        $this->info("Evidentia has started successfully. Enjoy!");
    }
}
