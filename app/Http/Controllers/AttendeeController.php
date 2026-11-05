<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckRoles;
use Illuminate\Support\Facades\Auth;

class AttendeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':STUDENT');
    }

    public function list()
    {
        $attendees = Auth::user()->attendees;

        $events_update = \Config::events_uploaded_timestamp();
        $attendees_update = \Config::attendees_uploaded_timestamp();

        return view('attendee.list',
            ['attendees' => $attendees,
                'events_update' => $events_update, 'attendees_update' => $attendees_update]);
    }
}
