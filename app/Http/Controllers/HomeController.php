<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{

    public function __construct()
    {

    }



    public function index()
    {
        $instance = \Instantiation::instance();
        return view('home',['instance' => $instance]);
    }
}
