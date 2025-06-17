<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function mailbox()
    {
        
        return view('message.mailbox',
            ['instance' => $instance]);
    }
}
