<?php

namespace App\Http\Services;

use App\Http\Resources\InstanceResource;
use App\Models\Instance;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InstanceService extends Service
{
    public function __construct()
    {

        parent::__construct(Instance::class, InstanceResource::class);

        $this->rules = [
            'name' => ['required', 'min:5', 'max:20', Rule::unique('instances')->ignore($this->request->_id)],
            'route' => ['required', 'alpha_num', Rule::unique('instances')->ignore($this->request->_id)],
            'host' => 'required',
            'port' => 'required|numeric',
            'database' => ['required', 'alpha_num', Rule::unique('instances')->ignore($this->request->_id)],
            'username' => 'required',
            'password' => 'required',
        ];

    }

    public function test_connection($name, $route, $host, $port, $username, $password): bool
    {

        $successfully = false;

        $instance = new Instance();
        $instance->name = $name;
        $instance->route = $route;
        $instance->host = $host;
        $instance->port = $port;
        $instance->username = $username;
        $instance->password = $password;

        \Instantiation::set($instance);

        try {
            DB::connection()->getPdo();
            $successfully = true;
        } catch (\Exception $e) {
            \Instantiation::set_default_connection();
        }

        \Instantiation::set_default_connection();

        return $successfully;
    }

    public function instance_database($instance)
    {
        DB::connection()->getPdo()->exec("CREATE DATABASE IF NOT EXISTS `$instance->database`");
        DB::connection()->getPdo()->exec("ALTER SCHEMA `$instance->database` DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci");

        \Instantiation::set($instance);

        try {
            DB::connection()->getPdo();
            \Instantiation::migrate();
            Artisan::call('db:seed',
                [
                    '--class' => 'SampleSeeder',
                    '--database' => 'instance'
                ]);
        } catch (\Exception $e) {

            \Instantiation::set_default_connection();
            DB::statement("DROP DATABASE `$instance->database`");
            $instance->delete();

        }

        \Instantiation::set_default_connection();
    }

    public function delete_database($instance)
    {
        DB::statement("DROP DATABASE `$instance->database`");
    }

    public function delete_instance_files($instance)
    {
        exec("rm -r /var/www/storage/app/21 2>/dev/null");
        exec("rm -r /var/www/storage/app/livewire-tmp 2>/dev/null");
    }
}