<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** The activity log and the histories; filled in by its own batch. */
class ActivityController extends Controller
{
    public function index(Request $request)
    {
        abort(404);
    }

    public function export(Request $request)
    {
        abort(404);
    }

    public function mine(Request $request)
    {
        abort(404);
    }

    public function evidence(Request $request, int $id)
    {
        abort(404);
    }

    public function minutes(Request $request, int $id)
    {
        abort(404);
    }

    public function meetingRequest(Request $request, int $id)
    {
        abort(404);
    }
}
