<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingSecretaryController extends Controller
{
    public function list()
    {
        $instance = \Instantiation::instance();

        $defaultlists = Auth::user()->secretary->comittee->default_lists;

        return view('meeting.list',
            ['instance' => $instance, 'defaultlists' => $defaultlists]);
    }
}
