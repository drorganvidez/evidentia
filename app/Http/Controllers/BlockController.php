<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function block()
    {
        if(Auth::user()->block){
            return view('block');
        }else{
            return redirect()->route('home',\Instantiation::instance());
        }

    }

}
