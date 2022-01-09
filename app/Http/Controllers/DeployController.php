<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\Instance;

class DeployController extends Controller
{

    public function deploy(Request $request){

        $username = $request->input('username');
        $password = $request->input('password');

        $username_env = env('DB_USERNAME');
        $passsword_env = env('DB_PASSWORD');

        $check_username = strcmp($username,$username_env) == 0;
        $check_password = strcmp($password,$passsword_env) == 0;

        if($check_username && $check_password){
            
            Artisan::call('evidentia:update');
            return response('Evidentia actualizado correctamente', 200);
        }
        return response('Evidentia actualizado erroneamente', 200);


    }
}
