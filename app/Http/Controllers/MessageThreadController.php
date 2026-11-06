<?php

namespace App\Http\Controllers;

use App\Models\MessageThread;
use Illuminate\Http\Request;

/** Message threads; filled in by its own batch. */
class MessageThreadController extends Controller
{
    public function inbox(Request $request)
    {
        abort(404);
    }

    public function mine(Request $request)
    {
        abort(404);
    }

    public function create(Request $request)
    {
        abort(404);
    }

    public function store(Request $request)
    {
        abort(404);
    }

    public function show(Request $request, MessageThread $thread)
    {
        abort(404);
    }

    public function reply(Request $request, MessageThread $thread)
    {
        abort(404);
    }

    public function resolve(Request $request, MessageThread $thread)
    {
        abort(404);
    }

    public function reopen(Request $request, MessageThread $thread)
    {
        abort(404);
    }
}
