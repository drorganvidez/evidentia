<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Http\Controllers\Concerns\PaginatesLists;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\User;
use App\Services\EvidenceReview;
use App\Services\ReviewQueue;
use App\Services\SelfReview;
use App\Support\ListPage;
use App\Support\ListSort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * A review queue with one tab per state and the accept and reject actions: the coordination of a committee
 * (EvidenceCoordinatorController) and the presidency (PresidencyReviewController). App\Services\ReviewQueue decides
 * what each queue holds and who may review each evidence.
 */
abstract class ReviewQueueController extends Controller
{
    use PaginatesLists;

    private const STATES = ['pending' => 'PENDING', 'accepted' => 'ACCEPTED', 'rejected' => 'REJECTED'];

    public function __construct(protected ReviewQueue $queue) {}

    /** ReviewQueue::COORDINATION or ReviewQueue::PRESIDENCY */
    abstract protected function kind(): string;

    /** Last versions sent for review that the person reviews in this queue, never their own. */
    abstract protected function evidences(User $user): Builder;

    /** The committee of the queue, for its chip. */
    abstract protected function committee(User $user): ?Committee;

    abstract protected function reviews(User $user, Evidence $evidence): bool;

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

    public function accept($id)
    {
        $evidence = $this->reviewable($id);
        $this->deny_own($evidence, ActivityAction::EvidenceAccepted);
        abort_unless($this->reviews(Auth::user(), $evidence), 404);

        app(EvidenceReview::class)->accept($evidence);

        return redirect()->route(ReviewQueue::routes($this->kind())['accepted'])->with('success', 'Evidencia aceptada con éxito.');
    }

    public function reject(Request $request)
    {
        $evidence = $this->reviewable($request->_id);

        if (blank($request->input('reasonrejection'))) {
            return back()->with('error', 'Escribe el motivo del rechazo.');
        }

        $this->deny_own($evidence, ActivityAction::EvidenceRejected);
        abort_unless($this->reviews(Auth::user(), $evidence), 404);

        app(EvidenceReview::class)->reject($evidence, $request->input('reasonrejection'));

        return redirect()->route(ReviewQueue::routes($this->kind())['rejected'])->with('success', 'Evidencia rechazada con éxito.');
    }

    /** One tab of the queue, in pages, with the counts and the hours of the whole search. */
    private function list(Request $request, string $type)
    {
        $user = Auth::user();
        $columns = ['title', 'user.name', 'user.surname', 'user.username'];
        if ($this->kind() === ReviewQueue::PRESIDENCY) {
            $columns[] = 'committee.name';
        }
        // orden en la URL; por defecto, la última modificación primero
        $sort = ListSort::from($request, array_filter([
            'title' => 'title',
            'student' => ['user.surname', 'user.name'],
            'committee' => $this->kind() === ReviewQueue::PRESIDENCY ? 'committee.name' : null,
            'hours' => 'hours',
            'created_at' => ListSort::desc('created_at'),
            'status' => ListSort::rank('status', ['PENDING', 'ACCEPTED', 'REJECTED']),
            'updated' => ListSort::desc('updated_at'),
        ]), 'updated');
        $scope = fn (): Builder => $this->searchList($this->evidences($user), $request, $columns);
        $counts = $scope()->reorder()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $filtered = $scope()->when($type !== 'all', fn (Builder $query) => $query->where('status', self::STATES[$type]));
        $hours = (float) (clone $filtered)->reorder()->sum('hours');
        $evidences = $this->paginateList(
            $sort->apply($filtered->with(['user', 'committee', 'reasonRejection'])),
            $request,
        );

        return view('evidence.coordinator.list', [
            'evidences' => $evidences,
            'type' => $type,
            'kind' => $this->kind(),
            'committee' => $this->committee($user),
            'routes' => ReviewQueue::routes($this->kind()),
            'counts' => ['all' => (int) $counts->sum()] + array_map(fn (string $status): int => (int) ($counts[$status] ?? 0), self::STATES),
            'hours' => $hours,
            'term' => ListPage::term($request),
            'sort' => $sort,
        ]);
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
}
