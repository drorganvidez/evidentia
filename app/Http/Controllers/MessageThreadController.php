<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PaginatesLists;
use App\Http\Requests\ReplyMessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\MessageThread;
use App\Services\Activity\ActivityAccess;
use App\Services\Activity\SubjectInfo;
use App\Services\Messaging\Audience;
use App\Services\Messaging\Messaging;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Inbox of the people in charge, the threads each person wrote, the form of a new message and the thread itself.
 */
class MessageThreadController extends Controller
{
    use PaginatesLists;

    private const STATES = ['open', 'resolved', 'all'];

    public function __construct(private Messaging $messaging) {}

    public function inbox(Request $request)
    {
        $me = $request->user();
        $audiences = Audience::of($me)->map(fn (Audience $audience) => ['value' => $audience->key(), 'label' => $audience->label()]);
        // The management reads every thread; the president without taking part in those of others (MessageThreadPolicy)
        if (Roles::isManager($me)) {
            $audiences->push(['value' => 'all', 'label' => 'Todos los hilos']);
        }

        $to = in_array($request->query('to'), $audiences->pluck('value')->all(), true) ? $request->query('to') : '';
        $committees = Committee::orderBy('name')->get(['id', 'name']);
        $committee = in_array((int) $request->query('committee'), $committees->pluck('id')->all(), true) ? (int) $request->query('committee') : null;
        $q = trim((string) $request->query('q', ''));
        $state = in_array($request->query('state'), self::STATES, true) ? $request->query('state') : 'open';
        $audience = $to !== '' && $to !== 'all' ? Audience::fromKey($to) : null;

        $base = fn (): Builder => MessageThread::query()
            ->when($to === 'all', fn (Builder $query) => $query->where('author_id', '!=', $me->id), fn (Builder $query) => $query->inbox($me))
            ->when($audience !== null, fn (Builder $query) => $query
                ->where('recipient_role', $audience->role)
                ->when($audience->committeeId !== null, fn (Builder $query) => $query->where('committee_id', $audience->committeeId)))
            ->when($committee !== null, fn (Builder $query) => $query->where('committee_id', $committee))
            ->when($q !== '', fn (Builder $query) => $query->search($q));

        $threads = $this->paginateList($base()
            ->when($state !== 'all', fn (Builder $query) => $query->where('state', $state))
            ->with(['author', 'lastMessage', 'committee'])
            ->latest('last_message_at')
            ->latest('id'), $request);

        return view('messages.inbox', [
            'me' => $me,
            'threads' => $threads,
            'state' => $state,
            'to' => $to,
            'committee' => $committee,
            'q' => $q,
            'audiences' => $audiences->all(),
            'committees' => $committees->map(fn (Committee $committee) => ['value' => $committee->id, 'label' => $committee->name])->all(),
            'active' => count(array_filter([$to, $committee, $q])),
            'query' => array_filter(['to' => $to, 'committee' => $committee, 'q' => $q]),
            'counts' => ['open' => $base()->open()->count(), 'resolved' => $base()->resolved()->count()],
            'unread' => MessageThread::unreadInboxCount($me),
        ]);
    }

    public function mine(Request $request)
    {
        $me = $request->user();
        $state = in_array($request->query('state'), self::STATES, true) ? $request->query('state') : 'all';
        $base = fn (): Builder => MessageThread::query()->where('author_id', $me->id);

        return view('messages.mine', [
            'me' => $me,
            'state' => $state,
            'threads' => $this->paginateList($base()
                ->when($state !== 'all', fn (Builder $query) => $query->where('state', $state))
                ->with(['lastMessage', 'committee'])
                ->latest('last_message_at')
                ->latest('id'), $request),
            'counts' => ['open' => $base()->open()->count(), 'resolved' => $base()->resolved()->count(), 'all' => $base()->count()],
            'unread' => MessageThread::unreadMineCount($me),
        ]);
    }

    public function create(Request $request)
    {
        $me = $request->user();
        [$aboutType, $aboutId] = $this->aboutFromQuery($request);
        $about = $aboutType === null ? null : StoreMessageRequest::resolveAbout($aboutType, $aboutId, $me);

        $recipients = Audience::optionsFor($me, $about)->all();
        $values = array_column($recipients, 'value');
        $preselected = match (true) {
            in_array($request->query('to'), $values, true) => $request->query('to'),
            $about !== null && $values !== [] => $values[0],
            default => '',
        };
        $suggestion = $about === null && $preselected === 'LECTURE';
        // A nudge from the management arrives with its subject and its text already written
        $asked = fn (string $key, int $max): ?string => is_string($request->query($key)) && trim($request->query($key)) !== ''
            ? Str::limit(trim($request->query($key)), $max, '')
            : null;
        $title = $suggestion ? 'Sugerencias al profesorado' : 'Nuevo mensaje';
        $aboutUrl = $about === null ? null : $this->aboutUrl($about);

        return view('messages.create', [
            'title' => $title,
            'about' => $about,
            'aboutType' => $aboutType,
            'aboutId' => $about === null ? null : $about->getKey(),
            'aboutTitle' => $about === null ? null : SubjectInfo::title($about),
            'aboutUrl' => $aboutUrl,
            'recipients' => $recipients,
            'preselected' => $preselected,
            'subjectDefault' => $asked('subject', 255) ?? match (true) {
                $about !== null => 'Sobre «'.SubjectInfo::title($about).'»',
                $suggestion => 'Sugerencia',
                default => null,
            },
            'bodyDefault' => $asked('body', 2000),
            'backHref' => $aboutUrl ?? route('messages.mine'),
        ]);
    }

    public function store(StoreMessageRequest $request)
    {
        $validated = $request->validated();
        $thread = $this->messaging->open(
            $request->user(),
            Audience::fromKey($validated['recipient']),
            $validated['subject'],
            $validated['body'],
            $request->about(),
        );

        return redirect()->route('messages.show', $thread)->with('success', 'Mensaje enviado.');
    }

    public function show(Request $request, MessageThread $thread)
    {
        $this->authorize('view', $thread);
        $me = $request->user();

        $thread->load(['messages.user', 'author', 'committee']);
        $this->messaging->markRead($thread, $me);
        $isAuthor = (int) $thread->author_id === (int) $me->id;
        $inbox = array_intersect(Roles::RESPONSIBLE, $me->roles->pluck('rol')->all()) !== [];

        return view('messages.show', [
            'me' => $me,
            'thread' => $thread,
            'isAuthor' => $isAuthor,
            'log' => ActivityLog::query()->about($thread)->historyVisibleTo($me, false)->with('user')->latest('id')->get(),
            'participants' => $thread->participants(),
            'access' => app(ActivityAccess::class),
            'navCurrent' => $isAuthor || ! $inbox ? 'messages.mine' : 'messages.inbox',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'href' => route('home')],
                $isAuthor || ! $inbox
                    ? ['label' => 'Mis mensajes', 'href' => route('messages.mine')]
                    : ['label' => 'Mensajes', 'href' => route('messages.inbox')],
                ['label' => $thread->subject],
            ],
        ]);
    }

    public function reply(ReplyMessageRequest $request, MessageThread $thread)
    {
        $this->authorize('reply', $thread);
        $this->messaging->reply($thread, $request->user(), $request->validated('body'));

        return redirect()->route('messages.show', $thread)->with('success', 'Respuesta enviada.');
    }

    public function resolve(Request $request, MessageThread $thread)
    {
        $this->authorize('resolve', $thread);
        $this->messaging->resolve($thread, $request->user());

        return redirect()->route('messages.show', $thread)->with('success', 'Hilo marcado como resuelto.');
    }

    public function reopen(Request $request, MessageThread $thread)
    {
        $this->authorize('reopen', $thread);
        $this->messaging->reopen($thread, $request->user());

        return redirect()->route('messages.show', $thread)->with('success', 'Hilo reabierto.');
    }

    /** @return array{0: ?string, 1: ?int} the object asked for in the query string (evidence, minutes or request) */
    private function aboutFromQuery(Request $request): array
    {
        foreach (['evidence' => 'evidence', 'minutes' => 'meeting_minutes', 'request' => 'meeting_request'] as $key => $type) {
            $id = $request->query($key);
            if (is_string($id) && ctype_digit($id)) {
                return [$type, (int) $id];
            }
        }

        return [null, null];
    }

    /** The page of the object, already checked to be within the reach of the author. */
    private function aboutUrl(Model $about): string
    {
        $route = match ($about->getMorphClass()) {
            'evidence' => 'activity.evidence',
            'meeting_minutes' => 'activity.minutes',
            default => 'activity.request',
        };

        return route($route, ['id' => $about->getKey()]);
    }
}
