<?php

namespace App\Http\Controllers;

use App\Models\Incidence;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class IncidenceCoordinatorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:COORDINATOR|SECRETARY');
    }

    /****************************************************************************
     * MANAGE INCIDENCES
     ****************************************************************************/

    public function all()
    {
        $instance = \Instantiation::instance();

        $coordinator = Auth::user()->coordinator;
        $comittee = $coordinator->comittee;
        $incidences = $comittee->incidences()->paginate(10);

        return view('incidence.coordinator.list',
            ['instance' => $instance, 'incidences' => $incidences, 'type' => 'all']);
    }
    public function pending()
    {
        $instance = \Instantiation::instance();

        $coordinator = Auth::user()->coordinator;
        $comittee = $coordinator->comittee;
        $incidences = $comittee->incidences_pending()->paginate(10);

        return view('incidence.coordinator.list',
            ['instance' => $instance, 'incidences' => $incidences, 'type' => 'pending']);
    }
    public function inreview()
    {
        $instance = \Instantiation::instance();

        $coordinator = Auth::user()->coordinator;
        $comittee = $coordinator->comittee;
        $incidences = $comittee->incidences_in_review()->paginate(10);

        return view('incidence.coordinator.list',
            ['instance' => $instance, 'incidences' => $incidences, 'type' => 'accepted']);
    }
    public function closed()
    {
        $instance = \Instantiation::instance();

        $coordinator = Auth::user()->coordinator;
        $comittee = $coordinator->comittee;
        $incidences = $comittee->incidences_closed()->paginate(10);

        return view('incidence.coordinator.list',
            ['instance' => $instance, 'incidences' => $incidences, 'type' => 'accepted']);
    }


    public function review($instance, $id){
        $instance = \Instantiation::instance();

        $incidence = Incidence::find($id);
        $incidence->status = 'IN REVIEW';
        $incidence->save();

        return redirect()->route('coordinator.incidence.list.all', $instance)->with('success', 'Incidencia en revisión.');
    }
    public function close(Request $request){

        $instance = \Instantiation::instance();

        $incidence = Incidence::find($request->_id);
        $incidence->status = 'CLOSED';
        $incidence->close_reason = $request->input('reasonrejection');
        $incidence->save();

        return redirect()->route('coordinator.incidence.list.all', $instance)->with('success', 'Incidencia cerrada con éxito.');
    }

  
}
