<?php

namespace App\Http\Controllers;

use App\Exports\CoordinatorEvidencesExport;
use App\Models\Evidence;
use App\Models\ReasonRejection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Middleware\CheckRoles;

class EvidenceCoordinatorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class . ':COORDINATOR,SECRETARY');
    }

    /****************************************************************************
     * MANAGE EVIDENCES
     ****************************************************************************/

    public function all()
    {
        

        $coordinator = Auth::user()->coordinator;
        $committee = $coordinator->committee;
        $evidences = $committee->evidencesNotDraft()->paginate(10);;

        return view('evidence.coordinator.list',
            ['evidences' => $evidences, 'type' => 'all']);
    }

    public function pending()
    {
        

        $coordinator = Auth::user()->coordinator;
        $committee = $coordinator->committee;
        $evidences = $committee->evidences_pending()->paginate(10);

        return view('evidence.coordinator.list',
            ['evidences' => $evidences, 'type' => 'pending']);
    }

    public function accepted()
    {
        

        $coordinator = Auth::user()->coordinator;
        $committee = $coordinator->committee;
        $evidences = $committee->evidences_accepted()->paginate(10);

        return view('evidence.coordinator.list',
            ['evidences' => $evidences, 'type' => 'accepted']);
    }

    public function rejected()
    {
        

        $coordinator = Auth::user()->coordinator;
        $committee = $coordinator->committee;
        $evidences = $committee->evidences_rejected()->paginate(10);

        return view('evidence.coordinator.list',
            ['evidences' => $evidences, 'type' => 'rejected']);
    }

    public function accept($id)
    {
        

        $evidence = Evidence::find($id);
        $evidence->status = 'ACCEPTED';
        $evidence->save();

        return redirect()->route('coordinator.evidence.list.accepted')->with('success', 'Evidencia aceptada con éxito.');
    }

    public function reject(Request $request)
    {
        

        $evidence = Evidence::find($request->_id);
        $evidence->status = 'REJECTED';
        $evidence->save();

        $reasonrejection = ReasonRejection::create([
            'reason' => $request->input('reasonrejection'),
            'evidence_id' => $evidence->id
        ]);
        $reasonrejection->save();

        return redirect()->route('coordinator.evidence.list.rejected')->with('success', 'Evidencia rechazada con éxito.');
    }

    public function evidences_export($type, $ext)
    {
        try {
            if (ob_get_level()) {
                ob_end_clean();
            }
            if (!in_array($ext, ['csv', 'pdf', 'xlsx'])) {
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new CoordinatorEvidencesExport($type), 'eventos-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }
}
