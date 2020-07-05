<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:LECTURE|PRESIDENT');
    }

    public function user_list()
    {
        $instance = \Instantiation::instance();
        $users = User::all();

        return view('manage.user_list',
            ['instance' => $instance, 'users' => $users]);
    }
}
