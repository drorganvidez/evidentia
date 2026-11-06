<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Corrections of the management; filled in by its own batch. */
class ManagementEvidenceController extends Controller
{
    public function move(Request $request)
    {
        abort(404);
    }

    public function review(Request $request)
    {
        abort(404);
    }
}
