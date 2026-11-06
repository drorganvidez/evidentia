<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\Committee;
use App\View\CommitteePanel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * «Mi comité», the workspace where a coordinator organises their committee, and its export. A coordinator without a
 * committee has nothing to organise and gets a 404.
 */
class CommitteeCoordinatorController extends Controller
{
    public function show(CommitteePanel $panel): View
    {
        return view('coordinator.committee', $panel->for($this->committee(), Auth::user()));
    }

    public function export(CommitteePanel $panel): Response
    {
        $committee = $this->committee();
        $members = $panel->members($committee);

        DB::transaction(fn () => Activity::record(ActivityAction::CommitteeExported, $committee, details: ['rows' => $members->count()]));

        return response($panel->csv($members), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="comite-'.Str::slug($committee->name).'-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    private function committee(): Committee
    {
        $committee = Auth::user()->coordinator?->committee;
        abort_if($committee === null, 404);

        return $committee;
    }
}
