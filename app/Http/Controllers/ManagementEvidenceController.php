<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Enums\Deadline;
use App\Models\Committee;
use App\Models\Evidence;
use App\Services\Activity\ActivityAccess;
use App\Services\Deadlines;
use App\Services\EvidenceMover;
use App\Services\EvidenceReview;
use App\Services\SelfReview;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Corrections of the management outside the flow of the committees: moving any evidence that is not a draft and
 * accepting or rejecting it, also after the deadlines, which leaves the annotation flagged.
 */
class ManagementEvidenceController extends Controller
{
    public function __construct(private ActivityAccess $access, private Deadlines $deadlines) {}

    public function move(Request $request, EvidenceMover $mover)
    {
        $evidence = Evidence::findOrFail($request->input('_id'));
        abort_unless($this->access->canMoveAsManager($request->user(), $evidence), 404);

        $request->validate(
            ['committee' => ['required', 'integer', 'exists:committees,id', 'not_in:'.(int) $evidence->committee_id]],
            ['committee.not_in' => 'Elige un comité distinto del actual.'],
        );
        $to = Committee::findOrFail($request->integer('committee'));
        $late = ! $this->deadlines->isOpen(EvidenceMover::governingDeadline($evidence));

        try {
            $mover->move($evidence, $to, checkDeadline: true);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['committee' => $exception->getMessage()]);
        }

        return redirect()->route('activity.evidence', ['id' => $evidence->id])->with(...($late
            ? ['warning', 'Evidencia movida fuera de plazo. Queda señalada en el registro.']
            : ['success', 'Evidencia movida al comité '.$to->name.'.']));
    }

    public function review(Request $request, EvidenceReview $review)
    {
        $evidence = Evidence::findOrFail($request->input('_id'));
        if (SelfReview::violates($request->user(), $evidence)) {
            $attempted = $request->input('decision') === 'reject' ? ActivityAction::EvidenceRejected : ActivityAction::EvidenceAccepted;
            SelfReview::deny($evidence, $attempted, null, 'No puedes revisar una evidencia tuya. El intento queda anotado.');
        }
        abort_unless($this->access->canReviewAsManager($request->user(), $evidence), 404);

        $request->validate([
            'decision' => ['required', 'in:accept,reject'],
            'reasonrejection' => ['nullable', 'required_if:decision,reject', 'string', 'min:10', 'max:1000'],
        ], [
            'decision.in' => 'Elige aceptar o rechazar.',
            'reasonrejection.required_if' => 'El campo motivo del rechazo es obligatorio si rechazas.',
        ]);
        $late = ! $this->deadlines->isOpen(Deadline::ValidateEvidences);
        $reject = $request->input('decision') === 'reject';

        $reject
            ? $review->reject($evidence, (string) $request->input('reasonrejection'))
            : $review->accept($evidence);

        return redirect()->route('activity.evidence', ['id' => $evidence->id])->with(...($late
            ? ['warning', 'Revisión fuera de plazo. Queda señalada en el registro.']
            : ['success', $reject ? 'Evidencia rechazada.' : 'Evidencia aceptada.']));
    }
}
