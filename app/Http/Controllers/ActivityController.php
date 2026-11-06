<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Enums\Deadline;
use App\Facades\Activity;
use App\Http\Controllers\Concerns\PaginatesLists;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\MessageThread;
use App\Models\User;
use App\Services\Activity\ActivityAccess;
use App\Services\Activity\ActivityExport;
use App\Services\Activity\ActivityFilters;
use App\Services\Activity\ActivityPresenter;
use App\Services\Deadlines;
use App\Services\EvidenceMover;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The activity log (management whole, coordinators and secretaries their committee), the activity of each person and
 * the histories of an evidence, a minutes and a meeting request. Who sees what is decided here with ActivityAccess.
 */
class ActivityController extends Controller
{
    use PaginatesLists;

    public function __construct(private ActivityAccess $access, private ActivityPresenter $presenter) {}

    public function index(Request $request)
    {
        $me = $request->user();
        abort_unless($this->access->canViewLog($me), 404);

        $scope = $this->access->committeeScope($me);
        $filters = ActivityFilters::fromRequest($request, $scope);

        $entries = $this->paginateList($this->logQuery($scope, $filters)
            ->with(['user', 'targetUser', 'committee'])
            ->withCount('targets')
            ->when(
                $filters->request !== null,
                fn (Builder $query) => $query->oldest('created_at')->oldest('id'),
                fn (Builder $query) => $query->latest('created_at')->latest('id'),
            ), $request, config('activity.page_size'));

        $siblings = ActivityLog::query()
            ->whereIn('request_id', $entries->getCollection()->pluck('request_id')->filter()->unique()->values())
            ->when($scope !== null, fn (Builder $query) => $query->inCommittees($scope))
            ->groupBy('request_id')
            ->selectRaw('request_id, count(*) as n')
            ->pluck('n', 'request_id');

        $committees = Committee::query()
            ->when($scope !== null, fn (Builder $query) => $query->whereIn('id', $scope))
            ->orderBy('name')
            ->get(['id', 'name']);

        $title = $scope === null
            ? 'Registro de actividad'
            : 'Actividad de '.$committees->pluck('name')->join(', ', ' y ');

        return view('activity.index', [
            'title' => $title,
            'entries' => $entries,
            'siblings' => $siblings,
            'filters' => $filters,
            'me' => $me,
            'access' => $this->access,
            'presenter' => $this->presenter,
            'canExport' => $this->access->canExport($me),
            'types' => ActivityAction::options(),
            'people' => $this->people($scope),
            'roles' => collect(Roles::LABELS)->map(fn (string $label, string $role) => ['value' => $role, 'label' => $label])->values()->all(),
            'committees' => $committees->map(fn (Committee $committee) => ['value' => $committee->id, 'label' => $committee->name])->all(),
            'committeeLocked' => $scope !== null && count($scope) === 1,
            'committeeValue' => $scope !== null && count($scope) === 1 ? $scope[0] : $filters->committee,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $me = $request->user();
        abort_unless($this->access->canExport($me), 404);

        $filters = ActivityFilters::fromRequest($request, null);
        $query = $this->logQuery(null, $filters);

        Activity::record(
            ActivityAction::ExportActivity,
            details: ['filters' => $filters->query(), 'rows' => $query->clone()->count()],
        );

        return app(ActivityExport::class)->stream($query);
    }

    public function mine(Request $request)
    {
        $me = $request->user();
        $scope = in_array($request->query('scope'), ['own', 'about', 'access'], true) ? $request->query('scope') : 'all';

        $scoped = fn (string $scope): Builder => $this->mineQuery($me, $scope);
        $entries = $this->paginateList($scoped($scope)->with('user')->latest('id'), $request, 30);

        return view('activity.mine', [
            'me' => $me,
            'scope' => $scope,
            'entries' => $entries,
            'presenter' => $this->presenter,
            'counts' => collect(['all', 'own', 'about', 'access'])->mapWithKeys(fn (string $key) => [$key => $scoped($key)->count()])->all(),
        ]);
    }

    public function evidence(Request $request, int $id)
    {
        $me = $request->user();
        $head = Evidence::findOrFail($id)->headEvidence();
        abort_unless($this->access->canViewEvidence($me, $head), 404);

        $head->load(['user', 'committee', 'reasonRejection']);
        $deadlines = app(Deadlines::class);
        $isOwner = (int) $head->user_id === (int) $me->id;
        $isCoordinator = ! $isOwner && $me->hasRole('COORDINATOR') && (int) $me->coordinator?->committee_id === (int) $head->committee_id;
        $isManager = $this->access->isManager($me);

        $versions = $head->flowEvidences()->values();
        $root = $head->rootEvidence();
        $entries = ActivityLog::query()->about($head)
            ->historyVisibleTo($me, $this->access->seesFullEvidenceHistory($me, $head))
            ->with('user')
            ->latest('id')
            ->get();
        $threads = MessageThread::query()
            ->where('about_type', $head->getMorphClass())
            ->where('about_id', $root->id)
            ->visibleTo($me)
            ->latest('last_message_at')
            ->get();

        $moveRoute = match (true) {
            $isOwner && $this->access->canMoveAsStudent($me, $head) => route('evidence.move'),
            $this->access->canMoveAsCoordinator($me, $head) => route('coordinator.evidence.move'),
            ! $isOwner && $this->access->canMoveAsManager($me, $head) => route('management.evidence.move'),
            default => null,
        };
        $detailHref = $this->access->evidenceDetailHref($me, $head);
        $list = match (true) {
            $isOwner => ['label' => 'Mis evidencias', 'href' => route('evidence.list')],
            $isCoordinator => ['label' => 'Revisar evidencias', 'href' => route('coordinator.evidence.list.all')],
            $isManager => ['label' => 'Evidencias', 'href' => route(($me->hasRole('LECTURE') ? 'lecture' : 'president').'.evidence.list')],
            default => null,
        };

        return view('activity.evidence', [
            'me' => $me,
            'head' => $head,
            'root' => $root,
            'versions' => $versions,
            'entries' => $entries,
            'threads' => $threads,
            'steps' => $this->steps($head, $versions, $entries),
            'lastRejection' => $entries->firstWhere('action', ActivityAction::EvidenceRejected->value),
            'isOwner' => $isOwner,
            'detailHref' => $detailHref,
            'moveRoute' => $moveRoute,
            'moveDeadlineClosed' => $moveRoute !== null && ! $deadlines->isOpen(EvidenceMover::governingDeadline($head)),
            'committeeOptions' => Committee::where('id', '!=', $head->committee_id)->orderBy('name')->get(['id', 'name'])
                ->map(fn (Committee $committee) => ['value' => $committee->id, 'label' => $committee->name])->all(),
            'canReview' => $this->access->canReviewAsManager($me, $head),
            'validationOpen' => $deadlines->isOpen(Deadline::ValidateEvidences),
            'uploadOpen' => $deadlines->isOpen(Deadline::UploadEvidences),
            'inlineDeadline' => match (true) {
                $isOwner && in_array($head->status, ['DRAFT', 'REJECTED'], true) && $deadlines->isOpenForMenu(Deadline::UploadEvidences) => Deadline::UploadEvidences,
                $isCoordinator && $head->status === 'PENDING' && $deadlines->isOpenForMenu(Deadline::ValidateEvidences) => Deadline::ValidateEvidences,
                default => null,
            },
            'deadlines' => $deadlines,
            'access' => $this->access,
            'presenter' => $this->presenter,
            'navCurrent' => match (true) {
                $isOwner => 'evidence.list',
                $isCoordinator => 'coordinator.evidence.list.all',
                $isManager => ($me->hasRole('LECTURE') ? 'lecture' : 'president').'.evidence.list',
                default => null,
            },
            'breadcrumbs' => array_values(array_filter([
                ['label' => 'Inicio', 'href' => route('home')],
                $list,
                ['label' => $head->title, 'href' => $detailHref],
                ['label' => 'Historial'],
            ])),
        ]);
    }

    public function minutes(Request $request, int $id)
    {
        $me = $request->user();
        $minutes = MeetingMinutes::with(['meeting.users', 'meeting.committee', 'secretary.user', 'points.agreements'])->findOrFail($id);
        $canHistory = $this->access->canViewMinutesHistory($me, $minutes);
        $tab = $canHistory && $request->query('tab') === 'historial' ? 'historial' : 'detalle';
        $isSecretary = $minutes->secretary_id !== null && (int) $me->secretary?->id === (int) $minutes->secretary_id;
        $isAttendee = $minutes->meeting?->users->contains('id', $me->id) ?? false;

        return view('activity.minutes', [
            'me' => $me,
            'minutes' => $minutes,
            'meeting' => $minutes->meeting,
            'tab' => $tab,
            'canHistory' => $canHistory,
            'entries' => $tab === 'historial'
                ? ActivityLog::query()->about($minutes)->historyVisibleTo($me, $this->access->isManager($me))->with('user')->latest('id')->get()
                : collect(),
            'canEdit' => $isSecretary && app(Deadlines::class)->isOpen(Deadline::Meetings),
            'canWrite' => ! $isSecretary,
            'access' => $this->access,
            'navCurrent' => $this->meetingsNav($me, $isSecretary, $isAttendee),
        ]);
    }

    public function meetingRequest(Request $request, int $id)
    {
        $me = $request->user();
        $meetingRequest = MeetingRequest::with(['committee', 'secretary.user', 'diary.diaryPoints', 'signatureSheet.users'])->findOrFail($id);
        $canHistory = $this->access->canViewRequestHistory($me, $meetingRequest);
        $tab = $canHistory && $request->query('tab') === 'historial' ? 'historial' : 'detalle';
        $seesSignatures = $this->access->seesSignatures($me, $meetingRequest);
        $isSecretary = $meetingRequest->secretary_id !== null && (int) $me->secretary?->id === (int) $meetingRequest->secretary_id;

        $entries = collect();
        if ($tab === 'historial') {
            $full = $this->access->isManager($me);
            $sheet = $meetingRequest->signatureSheet;
            $entries = ActivityLog::query()
                ->where(fn (Builder $where) => $where
                    ->where(fn (Builder $about) => $about->about($meetingRequest))
                    ->when($sheet !== null, fn (Builder $where) => $where->orWhere(fn (Builder $about) => $about->about($sheet))))
                ->historyVisibleTo($me, $full)
                ->when(! $seesSignatures, fn (Builder $query) => $query->where('action', '!=', ActivityAction::SignatureSigned->value))
                ->with('user')
                ->latest('id')
                ->get();
        }

        return view('activity.request', [
            'me' => $me,
            'meetingRequest' => $meetingRequest,
            'tab' => $tab,
            'canHistory' => $canHistory,
            'seesSignatures' => $seesSignatures,
            'isSecretary' => $isSecretary,
            'entries' => $entries,
            'access' => $this->access,
            'navCurrent' => $this->meetingsNav($me, $isSecretary, false),
        ]);
    }

    /** The log narrowed to the committees of the viewer and to the filters, without order. */
    private function logQuery(?array $scope, ActivityFilters $filters): Builder
    {
        return ActivityLog::query()
            ->when($scope !== null, fn (Builder $query) => $query->inCommittees($scope))
            ->tap(fn (Builder $query) => $filters->apply($query));
    }

    /**
     * People of the filter: everybody for the management; for a committee only who appears in its annotations.
     *
     * @return list<array{value: int, label: string, hint: ?string}>
     */
    private function people(?array $scope): array
    {
        $ids = fn (string $column) => ActivityLog::query()->inCommittees($scope ?? [])->whereNotNull($column)->select($column);

        return User::query()
            ->when($scope !== null, fn (Builder $query) => $query->where(fn (Builder $where) => $where
                ->whereIn('id', $ids('user_id'))
                ->orWhereIn('id', $ids('target_user_id'))))
            ->orderBy('surname')
            ->orderBy('name')
            ->get(['id', 'name', 'surname', 'username'])
            ->map(fn (User $user) => ['value' => $user->id, 'label' => trim($user->surname.', '.$user->name, ', '), 'hint' => $user->username])
            ->all();
    }

    /** The four views of «Mi actividad»; every OR stays inside its own where. */
    private function mineQuery(User $me, string $scope): Builder
    {
        $access = ActivityAction::inGroup('access');
        $query = ActivityLog::query()->forUser($me)->where('action', '!=', ActivityAction::NotificationSent->value);

        return match ($scope) {
            'own' => $query->where('user_id', $me->id)->whereNotIn('action', $access),
            'about' => $query->where(fn (Builder $where) => $where->whereNull('user_id')->orWhere('user_id', '!=', $me->id))
                ->whereNotIn('action', $access),
            'access' => $query->access(),
            default => $query,
        };
    }

    /**
     * The four steps of an evidence, also right for the ones written before the log existed.
     *
     * @return array{sent: bool, draftAt: mixed, sentAt: mixed, rejected: bool, rejectedAt: mixed, acceptedAt: mixed}
     */
    private function steps(Evidence $head, Collection $versions, Collection $entries): array
    {
        $first = $versions->sortBy('created_at')->first();
        $reasons = $versions->flatMap(fn (Evidence $version) => $version->reasonRejections);
        $published = $entries->where('action', ActivityAction::EvidencePublished->value)->sortBy('id');
        $accepted = $entries->where('action', ActivityAction::EvidenceAccepted->value)->sortByDesc('id')->first()
            ?? $published->filter(fn (ActivityLog $entry) => ! empty($entry->details['automatic']))->last();

        return [
            'draftAt' => $first?->created_at,
            'sent' => $head->status !== 'DRAFT' || $versions->contains(fn (Evidence $version) => $version->status !== 'DRAFT'),
            'sentAt' => $published->first()?->created_at,
            'rejected' => $reasons->isNotEmpty(),
            'rejectedAt' => $reasons->sortBy('created_at')->last()?->created_at,
            'acceptedAt' => $head->status === 'ACCEPTED' ? ($accepted?->created_at ?? $head->updated_at) : null,
        ];
    }

    /** The entry of the menu a minutes or a meeting request belongs to for this person. */
    private function meetingsNav(User $me, bool $isSecretary, bool $isAttendee): ?string
    {
        return match (true) {
            $isSecretary => 'secretary.meeting.manage',
            $isAttendee => 'meeting.list',
            $this->access->isManager($me) => ($me->hasRole('LECTURE') ? 'lecture' : 'president').'.meeting.list',
            default => null,
        };
    }
}
