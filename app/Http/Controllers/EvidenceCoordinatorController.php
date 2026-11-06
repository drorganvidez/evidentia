<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Http\Controllers\Concerns\PaginatesLists;
use App\Http\Middleware\CheckRoles;
use App\Models\Evidence;
use App\Services\EvidenceReview;
use App\Services\SelfReview;
use App\Support\ListPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvidenceCoordinatorController extends Controller
{
    use PaginatesLists;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':COORDINATOR');
    }

    /****************************************************************************
     * MANAGE EVIDENCES
     ****************************************************************************/

    public function all(Request $request)
    {
        return $this->list($request, 'all');
    }

    public function pending(Request $request)
    {
        return $this->list($request, 'pending');
    }

    public function accepted(Request $request)
    {
        return $this->list($request, 'accepted');
    }

    public function rejected(Request $request)
    {
        return $this->list($request, 'rejected');
    }

    /** One tab of the evidences of the committee, in pages, with the counts and the hours of the whole search. */
    private function list(Request $request, string $type)
    {
        $committee = Auth::user()->coordinator->committee;
        $base = fn (): Builder => $this->searchList(
            $committee->evidences()->getQuery()->where('last', true)->where('status', '!=', 'DRAFT'),
            $request,
            ['title', 'user.name', 'user.surname', 'user.username'],
        );
        $statuses = ['pending' => 'PENDING', 'accepted' => 'ACCEPTED', 'rejected' => 'REJECTED'];
        $byStatus = $base()->groupBy('status')->selectRaw('status, count(*) as total')->pluck('total', 'status');
        $counts = ['all' => (int) $byStatus->sum()] + array_map(fn (string $status): int => (int) $byStatus->get($status, 0), $statuses);

        $filtered = $base()->when($type !== 'all', fn ($query) => $query->where('status', $statuses[$type]));
        $hours = (float) $filtered->clone()->sum('hours');
        $evidences = $this->paginateList(
            $filtered->with(['user', 'reasonRejection'])->orderByDesc('updated_at')->latest()->latest('id'),
            $request,
        );

        return view('evidence.coordinator.list', [
            'evidences' => $evidences, 'type' => $type, 'counts' => $counts, 'hours' => $hours, 'committee' => $committee,
            'term' => ListPage::term($request),
        ]);
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
