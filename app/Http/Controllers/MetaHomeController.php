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

        // si la instancia es null, es que no hay ninguna creada, así que redirigimos al panel
        // del admin
        if($instance == null){
            return redirect()->route('admin.login');
        }

        // si estoy logueado
        if(Auth::check()){
            return redirect()->route('instance.login',['instance' => $instance->route]);
        }


        return redirect()->route('instance.login',['instance' => $instance->route]);

    }
}
