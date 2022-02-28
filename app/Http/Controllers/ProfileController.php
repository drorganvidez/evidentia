<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function data()
    {
        return view('profile.data');
    }

    public function avatar()
    {
        return view('profile.avatar');
    }

    public function password()
    {
        return view('profile.password');
    }
}
