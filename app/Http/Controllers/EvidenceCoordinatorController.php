<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvidenceCoordinatorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:COORDINATOR|SECRETARY');
    }

    /****************************************************************************
     * MANAGE EVIDENCES
     ****************************************************************************/

    public function list()
    {
        $instance = \Instantiation::instance();

        $coordinator = Auth::user()->coordinator;
        $comittee = $coordinator->comittee;
        $evidences = $comittee->evidences_not_draft()->paginate(5);

        return view('evidence.coordinator.list',
            ['instance' => $instance, 'evidences' => $evidences]);
    }
}
