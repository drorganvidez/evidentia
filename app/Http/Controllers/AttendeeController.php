<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckRoles;
use App\Models\Attendee;
use App\Support\ListSort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':STUDENT');
    }

    public function list(Request $request)
    {
        // the newest event first by default
        $sort = ListSort::from($request, [
            'event' => 'event.name',
            'hours' => 'event.hours',
            'start' => ListSort::desc('event.start_datetime'),
            'end' => ListSort::desc('event.end_datetime'),
            'status' => ListSort::rank('status', Attendee::STATUS_ORDER),
        ], 'start');
        $attendees = $sort->apply(Auth::user()->attendees()->with('event'))->get();

        $events_update = \Config::events_uploaded_timestamp();
        $attendees_update = \Config::attendees_uploaded_timestamp();

        return view('attendee.list',
            ['attendees' => $attendees, 'sort' => $sort,
                'events_update' => $events_update, 'attendees_update' => $attendees_update]);
    }
}
