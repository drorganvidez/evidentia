<?php

namespace App\Http\Controllers;

use App\Evidence;
use App\Meeting;
use App\User;

class ManagementController extends Controller
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

    public function evidence_list()
    {
        $instance = \Instantiation::instance();
        $evidences = Evidence::evidences_not_draft();

        return view('manage.evidence_list',
            ['instance' => $instance, 'evidences' => $evidences]);
    }

    public function meeting_list()
    {
        $instance = \Instantiation::instance();
        $meetings = Meeting::all();

        return view('manage.meeting_list',
            ['instance' => $instance, 'meetings' => $meetings]);
    }
}
