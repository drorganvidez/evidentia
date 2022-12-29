<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class DashboardController extends Controller
{

    public function dashboard()
    {
        $instance = \Instantiation::instance();
        return view('dashboard',['instance' => $instance]);
    }
}