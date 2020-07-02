<?php

namespace App\Http\Controllers;

use App\Evidence;
use App\ReasonRejection;
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

    public function all()
    {
        $instance = \Instantiation::instance();

        $coordinator = Auth::user()->coordinator;
        $comittee = $coordinator->comittee;
        $evidences = $comittee->evidences_not_draft()->paginate(10);;

        return view('evidence.coordinator.list',
            ['instance' => $instance, 'evidences' => $evidences]);
    }

    public function pending()
    {
        $instance = \Instantiation::instance();

        $coordinator = Auth::user()->coordinator;
        $comittee = $coordinator->comittee;
        $evidences = $comittee->evidences_pending()->paginate(10);

        return view('evidence.coordinator.list',
            ['instance' => $instance, 'evidences' => $evidences]);
    }

    public function accepted()
    {
        $instance = \Instantiation::instance();

        $coordinator = Auth::user()->coordinator;
        $comittee = $coordinator->comittee;
        $evidences = $comittee->evidences_accepted()->paginate(10);

        return view('evidence.coordinator.list',
            ['instance' => $instance, 'evidences' => $evidences]);
    }

    public function rejected()
    {
        $instance = \Instantiation::instance();

        $coordinator = Auth::user()->coordinator;
        $comittee = $coordinator->comittee;
        $evidences = $comittee->evidences_rejected()->paginate(10);

        return view('evidence.coordinator.list',
            ['instance' => $instance, 'evidences' => $evidences]);
    }

    public function accept($instance,$id)
    {
        $instance = \Instantiation::instance();

        $evidence = Evidence::find($id);
        $evidence->status = 'ACCEPTED';
        $evidence->save();

        return redirect()->route('coordinator.evidence.list.accepted',$instance)->with('success', 'Evidencia aceptada con éxito.');
    }

    public function reject(Request $request)
    {
        $instance = \Instantiation::instance();

        $evidence = Evidence::find($request->_id);
        $evidence->status = 'REJECTED';
        $evidence->save();

        $reasonrejection = ReasonRejection::create([
            'reason' => $request->input('reasonrejection'),
            'evidence_id' => $evidence->id
        ]);
        $reasonrejection->save();

        return redirect()->route('coordinator.evidence.list.rejected',$instance)->with('success', 'Evidencia rechazada con éxito.');
    }
}
