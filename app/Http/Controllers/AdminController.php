<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function login()
    {

        if(Auth::check()){
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login_p(Request $request)
    {

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect()->route('admin.dashboard', ['admin' => 'admin']);
        }

        return back()->withInput()->with('error', 'Las credenciales no son válidas.');
    }

    public function logout(Request $request)
    {

        Auth::logout();

        return redirect()->route('instances.home');
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }
}