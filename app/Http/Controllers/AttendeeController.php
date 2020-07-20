<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:STUDENT');
    }

    public function list()
    {
        $instance = \Instantiation::instance();
        $attendees = Auth::user()->attendees;

        return view('attendee.list',
            ['instance' => $instance, 'attendees' => $attendees]);
    }
}
