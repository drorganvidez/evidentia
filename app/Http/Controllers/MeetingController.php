<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    public function list()
    {

        $instance = \Instantiation::instance();

        $meetings = Auth::user()->meetings()->paginate(5);

        return view('meeting.mylist',
            ['instance' => $instance, 'meetings' => $meetings]);
    }
}
