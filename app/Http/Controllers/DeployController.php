<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DeployController extends Controller
{
    public function deploy($token){
        $path = base_path();
        $handle = fopen($path."/token.txt", "r");
        $validate = False;
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                echo $line;
                if(strcmp(strval($token), strval($line)) == 0){
                    $validate = True;
                }
            }

            fclose($handle);
        } else {
            echo "error";
        }

        if($validate){
            Artisan::call("optimize:clear");
            Artisan::call("migrate");
            Artisan::call("db:seed");
        }
    }
}
