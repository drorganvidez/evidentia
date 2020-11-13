<?php

namespace App\Http\Controllers;

use App\Instance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MetaHomeController extends Controller
{
    public function home()
    {

        // obtenemos la última instancia creada
        $instance = Instance::orderBy('created_at', 'desc')->first();

        // si estoy logueado
        if(Auth::check()){
            return redirect()->route('instance.login',['instance' => $instance->route]);
        }

        return redirect()->route('instance.login',['instance' => $instance->route]);

    }
}
