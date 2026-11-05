<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckRoles;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':PRESIDENT,COORDINATOR,REGISTER_COORDINATOR,SECRETARY,STUDENT');
    }

    public function list()
    {

        $meetings = Auth::user()->meetings()->get();

        return view('meeting.mylist',
            ['meetings' => $meetings]);
    }
}
