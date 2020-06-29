<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class GeneralPurposeController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function users_all()
    {
        return User::all();
    }
}
