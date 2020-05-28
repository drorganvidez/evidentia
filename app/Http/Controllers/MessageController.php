<?php

namespace App\Http\Controllers;

use App\Evidence;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function mailbox()
    {
        $instance = \Instantiation::instance();
        return view('message.mailbox',
            ['instance' => $instance]);
    }
}
