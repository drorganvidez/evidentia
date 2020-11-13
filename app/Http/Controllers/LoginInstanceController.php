<?php

namespace App\Http\Controllers;

use App\Instance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class LoginInstanceController extends Controller
{

    public function login()
    {

        if(Auth::check()){
            return redirect()->route('home',['instance' => \Instantiation::instance()]);
        }

        \Instantiation::set_default_connection();
        $instances = Instance::all();
        \Instantiation::set_default_instance();

        if(count($instances) == 0){
            return redirect()->route('admin.login');
        }

        return view('auth.login',['instances' => $instances]);
    }

    public function login_p(Request $request)
    {

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            return redirect()->route('home',['instance' => \Instantiation::instance()]);
        }

        return back()->withInput()->with('error', 'Las credenciales no son válidas.');
    }

    public function logout(Request $request)
    {

        Auth::logout();

        return redirect()->route('home',['instance' => \Instantiation::instance()]);
    }
}
