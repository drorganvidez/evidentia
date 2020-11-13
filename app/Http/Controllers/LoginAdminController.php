<?php

namespace App\Http\Controllers;

use App\Instance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginAdminController extends Controller
{
    public function login()
    {

        if(Auth::check()){
            return redirect()->route('admin.home');
        }

        return view('admin.login');
    }

    public function login_p(Request $request)
    {

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect()->route('admin.home');
        }

        return back()->withInput()->with('error', 'Las credenciales no son válidas.');
    }

    public function logout(Request $request)
    {

        Auth::logout();

        return redirect()->route('instances.home');
    }
}
