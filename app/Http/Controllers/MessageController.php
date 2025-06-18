<?php

namespace App\Http\Controllers;

class MessageController extends Controller
{
    public function mailbox()
    {

        return view('message.mailbox',
            []);
    }
}
