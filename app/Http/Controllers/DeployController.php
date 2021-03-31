<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\Instance;

class DeployController extends Controller
{
    public function validate_token($token){
        $path = base_path();
        $handle = fopen($path."/token.txt", "r");
        $validate = False;
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if(strcmp(strval($token), strval($line)) == 0){
                    $validate = True;
                }
            }

            fclose($handle);
        } else {
            echo "error";
        }

        return $validate;
    }

    public function deploy($token){

        if($this->validate_token($token)){
            Artisan::call("optimize:clear");
            Artisan::call("migrate");
            Artisan::call("db:seed");
        }

        return redirect()->route('instances.home');
    }

    public function deploy_default_instance($token){

        if($this->validate_token($token)) {
            $instance = Instance::all()->first();
            \Instantiation::set($instance);

            // creamos las tablas
            Artisan::call('migrate',
                [
                    '--path' => 'database/migrations/instances',
                    '--database' => 'instance'
                ]);
            $output = Artisan::output();

            // la populamos con SampleSeeder
            Artisan::call('db:seed',
                [
                    '--class' => 'SampleSeeder',
                    '--database' => 'instance'
                ]);
            $output = Artisan::output();

            \Instantiation::set_default_connection();
        }

        return redirect()->route('instances.home');
    }
}
