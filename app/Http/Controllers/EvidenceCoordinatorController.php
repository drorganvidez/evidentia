<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Http\Middleware\CheckRoles;
use App\Models\Evidence;
use App\Services\EvidenceReview;
use App\Services\SelfReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvidenceCoordinatorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':COORDINATOR');
    }

    /****************************************************************************
     * MANAGE EVIDENCES
     ****************************************************************************/

    public function all()
    {

        $coordinator = Auth::user()->coordinator;
        $committee = $coordinator->committee;
        $evidences = $committee->evidencesNotDraft()->get();

        return view('evidence.coordinator.list',
            ['evidences' => $evidences, 'type' => 'all']);
    }

    public function pending()
    {

        $coordinator = Auth::user()->coordinator;
        $committee = $coordinator->committee;
        $evidences = $committee->evidencesPending()->get();

        return view('evidence.coordinator.list',
            ['evidences' => $evidences, 'type' => 'pending']);
    }

    public function accepted()
    {

        $coordinator = Auth::user()->coordinator;
        $committee = $coordinator->committee;
        $evidences = $committee->evidencesAccepted()->get();

        return view('evidence.coordinator.list',
            ['evidences' => $evidences, 'type' => 'accepted']);
    }

    public function rejected()
    {

        $coordinator = Auth::user()->coordinator;
        $committee = $coordinator->committee;
        $evidences = $committee->evidencesRejected()->get();

        return view('evidence.coordinator.list',
            ['evidences' => $evidences, 'type' => 'rejected']);
    }

    public function accept($id)
    {

        $evidence = $this->reviewable($id);
        $this->deny_own($evidence, ActivityAction::EvidenceAccepted);

        app(EvidenceReview::class)->accept($evidence);

        return redirect()->route('coordinator.evidence.list.accepted')->with('success', 'Evidencia aceptada con éxito.');
    }

    // solo se revisa la última versión de una evidencia; las anteriores las ha sustituido el alumno
    private function reviewable($id): Evidence
    {
        $evidence = Evidence::findOrFail($id);

        if (! $evidence->last) {
            abort(404);
        }

        return $evidence;
    }

    // nadie revisa su propia evidencia: el intento se corta y queda anotado
    private function deny_own(Evidence $evidence, ActivityAction $attempted): void
    {
        if (SelfReview::violates(Auth::user(), $evidence)) {
            SelfReview::deny($evidence, $attempted, $evidence->user, 'No puedes aceptar ni rechazar una evidencia tuya. El intento queda anotado.');
        }
    }

    public function reject(Request $request)
    {

        $evidence = $this->reviewable($request->_id);

        if (blank($request->input('reasonrejection'))) {
            return back()->with('error', 'Escribe el motivo del rechazo.');
        }

        $this->deny_own($evidence, ActivityAction::EvidenceRejected);

        app(EvidenceReview::class)->reject($evidence, $request->input('reasonrejection'));

        return redirect()->route('coordinator.evidence.list.rejected')->with('success', 'Evidencia rechazada con éxito.');
    }
}
