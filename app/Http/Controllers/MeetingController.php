<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckRoles;
use App\Support\ListSort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':PRESIDENT,COORDINATOR,REGISTER_COORDINATOR,SECRETARY,STUDENT');
    }

    public function list(Request $request)
    {
        $sort = ListSort::from($request, [
            'title' => 'title',
            'committee' => 'committee.name',
            'hours' => 'hours',
            'datetime' => ListSort::desc('datetime'),
        ], 'datetime');
        $meetings = $sort->apply(Auth::user()->meetings()->with(['committee', 'meetingMinutes']))->get();

        return view('meeting.mylist',
            ['meetings' => $meetings, 'sort' => $sort]);
    }
}
