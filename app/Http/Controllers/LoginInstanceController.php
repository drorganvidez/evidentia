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
        $instance = \Instantiation::instance();
        return view('auth.login',['instance' => $instance]);
    }

    public function login_p(Request $request)
    {

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            return redirect()->intended('dashboard');
        }

        return back()->withInput()->with('error', 'Las credenciales no son válidas.');
    }
}
