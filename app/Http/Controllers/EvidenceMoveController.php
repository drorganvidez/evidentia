<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Models\Committee;
use App\Models\Evidence;
use App\Services\Activity\ActivityAccess;
use App\Services\EvidenceMover;
use App\Services\SelfReview;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Moves an evidence to another committee: its author while uploads are open (draft, rejected or pending) and the
 * coordinator of its committee while validation is open (pending ones of others). The deadlines are checked by the
 * middleware of each route.
 */
class EvidenceMoveController extends Controller
{
    public function __construct(private EvidenceMover $mover, private ActivityAccess $access) {}

    public function student(Request $request)
    {
        $evidence = Evidence::findOrFail($request->input('_id'));
        abort_unless($this->access->canMoveAsStudent($request->user(), $evidence), 404);
        $to = $this->destination($request, $evidence);

        $this->move($evidence, $to, 'STUDENT');

        return redirect()->route('activity.evidence', ['id' => $evidence->id])
            ->with('success', 'Evidencia movida al comité '.$to->name.'.');
    }

    public function coordinator(Request $request)
    {
        $evidence = Evidence::findOrFail($request->input('_id'));
        if (SelfReview::violates($request->user(), $evidence)) {
            SelfReview::deny($evidence, ActivityAction::EvidenceMoved, null, 'No puedes mover desde la revisión una evidencia tuya. El intento queda anotado.');
        }
        abort_unless($this->access->canMoveAsCoordinator($request->user(), $evidence), 404);
        $to = $this->destination($request, $evidence);

        $this->move($evidence, $to, 'COORDINATOR');

        return redirect()->route('coordinator.evidence.list.pending')
            ->with('success', 'Evidencia enviada al comité '.$to->name.'.');
    }

    /** The committee chosen, never the current one. */
    private function destination(Request $request, Evidence $evidence): Committee
    {
        $request->validate(
            ['committee' => ['required', 'integer', 'exists:committees,id', 'not_in:'.(int) $evidence->committee_id]],
            ['committee.not_in' => 'Elige un comité distinto del actual.'],
        );

        return Committee::findOrFail($request->integer('committee'));
    }

    private function move(Evidence $evidence, Committee $to, string $role): void
    {
        try {
            $this->mover->move($evidence, $to, $role);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['committee' => $exception->getMessage()]);
        }
    }
}
