<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function home()
    {
        if(Auth::check()){
            return view('admin.home');
        }

        return redirect()->route('admin.login');
    }
}
