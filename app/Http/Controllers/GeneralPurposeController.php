<?php

namespace App\Http\Controllers;

use App\Models\User;

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
