<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\View\CommitteeOverview;
use Illuminate\Http\Request;

/** The page of one committee for the president and the lecturers: everything that happens in it. */
class CommitteeOverviewController extends Controller
{
    public function show(Request $request, CommitteeOverview $overview, int $id)
    {
        $committee = Committee::findOrFail($id);
        $prefix = $request->user()->hasRole('LECTURE') ? 'lecture' : 'president';

        return view('manage.committee_view', $overview->for($committee, $request->user()) + ['prefix' => $prefix]);
    }
}
