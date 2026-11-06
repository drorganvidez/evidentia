<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Moves of an evidence by its author and its coordinator; filled in by its own batch. */
class EvidenceMoveController extends Controller
{
    public function student(Request $request)
    {
        abort(404);
    }

    public function coordinator(Request $request)
    {
        abort(404);
    }
}
