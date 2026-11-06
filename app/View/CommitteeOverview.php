<?php

namespace App\View;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\MeetingRequest;
use App\Models\MessageThread;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Everything the management may want to know about one committee: the figures and the members of «Mi comité»
 * (CommitteePanel), its team with when each one last came in and acted, where its hours come from, its latest
 * evidences and meetings, the messages it has not answered and the signals of what is going wrong, each one with
 * the way to nudge the person in charge.
 */
final class CommitteeOverview
{
    /** Days an evidence may wait in the queue before it is late */
    public const LATE_DAYS = 7;

    /** Days without a meeting before the committee looks quiet */
    public const QUIET_DAYS = 21;

    /** Days a thread to the committee may wait for an answer */
    public const UNANSWERED_DAYS = 3;

    public function __construct(private CommitteePanel $panel) {}

    /** @return array<string, mixed> */
    public function for(Committee $committee, User $viewer): array
    {
        $data = $this->panel->for($committee, $viewer);
        $team = $this->team($committee, $data['coordinators'], $data['secretaries']);

        return $data + [
            'team' => $team,
            'origins' => $this->origins($committee),
            'evidences' => Evidence::where('committee_id', $committee->id)->where('last', true)
                ->whereIn('status', ['PENDING', 'ACCEPTED', 'REJECTED'])
                ->with('user')->orderByDesc('updated_at')->orderByDesc('id')->limit(8)->get(),
            'meetings' => Meeting::where('committee_id', $committee->id)->withCount('users')->with('meetingMinutes')
                ->orderByDesc('datetime')->orderByDesc('id')->limit(8)->get(),
            'threads' => $this->threads($committee)->orderByDesc('last_message_at')->limit(5)->get(),
            'signals' => $this->signals($committee),
        ];
    }

    /**
     * One row per committee for the list of the management: its team, its queue, the hours it credits, its meetings,
     * its latest annotation and its signals.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function summaries(): Collection
    {
        $meetingHours = DB::table('meeting_user')->join('meetings', 'meetings.id', '=', 'meeting_user.meeting_id')
            ->groupBy('meetings.committee_id')->selectRaw('meetings.committee_id as id, sum(meetings.hours) as hours')->pluck('hours', 'id');
        $bonusHours = DB::table('bonus_user')->join('bonuses', 'bonuses.id', '=', 'bonus_user.bonus_id')
            ->groupBy('bonuses.committee_id')->selectRaw('bonuses.committee_id as id, sum(bonuses.hours) as hours')->pluck('hours', 'id');
        $lastActivity = ActivityLog::whereNotNull('committee_id')->groupBy('committee_id')
            ->selectRaw('committee_id, max(created_at) as at')->pluck('at', 'committee_id');

        return Committee::with(['coordinators.user', 'secretaries.user'])
            ->withCount([
                'evidences as pending_count' => fn ($query) => $query->where('last', true)->where('status', 'PENDING'),
                'evidences as sent_count' => fn ($query) => $query->where('last', true)->whereIn('status', ['PENDING', 'ACCEPTED', 'REJECTED']),
                'meetings',
            ])
            ->withSum(['evidences as accepted_hours' => fn ($query) => $query->where('last', true)->where('status', 'ACCEPTED')], 'hours')
            ->withMin(['evidences as oldest_pending' => fn ($query) => $query->where('last', true)->where('status', 'PENDING')], 'updated_at')
            ->orderBy('name')->get()
            ->map(fn (Committee $committee): array => [
                'committee' => $committee,
                'coordinators' => $committee->coordinators->pluck('user')->filter()->values(),
                'secretaries' => $committee->secretaries->pluck('user')->filter()->values(),
                'pending' => (int) $committee->pending_count,
                'pendingDays' => $committee->oldest_pending === null ? null : (int) Carbon::parse($committee->oldest_pending)->diffInDays(now()),
                'sent' => (int) $committee->sent_count,
                'meetings' => (int) $committee->meetings_count,
                'hours' => (float) $committee->accepted_hours + (float) ($meetingHours[$committee->id] ?? 0) + (float) ($bonusHours[$committee->id] ?? 0),
                'last' => isset($lastActivity[$committee->id]) ? Carbon::parse($lastActivity[$committee->id]) : null,
                'signals' => $this->signals($committee),
            ]);
    }

    /**
     * What is going wrong in the committee, the most serious first: nobody in charge, evidences waiting too long,
     * meeting requests already held without their minutes, messages without an answer, no meetings for weeks and
     * rejected evidences nobody corrected. Each one says who to nudge and how.
     *
     * @return list<array{key: string, tone: string, title: string, detail: string, action: ?array{label: string, href: string, icon: string}}>
     */
    public function signals(Committee $committee): array
    {
        $committee->loadMissing(['coordinators.user', 'secretaries.user']);
        $coordinated = $committee->coordinators->pluck('user')->filter()->isNotEmpty();
        $secretaried = $committee->secretaries->pluck('user')->filter()->isNotEmpty();
        $nudge = fn (string $role, string $subject, string $body, array $about = []): ?array => ($role === 'COORDINATOR' ? $coordinated : $secretaried)
            ? [
                'label' => $role === 'COORDINATOR' ? 'Escribir a la coordinación' : 'Escribir a la secretaría',
                'href' => route('messages.create', $about + ['to' => $role.':'.$committee->id, 'subject' => $subject, 'body' => $body]),
                'icon' => 'send',
            ]
            : null;
        $signals = [];

        if (! $coordinated) {
            $signals[] = ['key' => 'no-coordinator', 'tone' => 'rejected', 'title' => 'Sin coordinación',
                'detail' => 'Nadie revisa las evidencias de '.$committee->name, 'action' => $this->assign()];
        }
        if (! $secretaried) {
            $signals[] = ['key' => 'no-secretary', 'tone' => 'pending', 'title' => 'Sin secretaría',
                'detail' => 'Nadie convoca ni levanta las actas de '.$committee->name, 'action' => $this->assign()];
        }

        $late = Evidence::where('committee_id', $committee->id)->where('last', true)->where('status', 'PENDING')
            ->where('updated_at', '<', now()->subDays(self::LATE_DAYS));
        $lateCount = (clone $late)->count();
        if ($lateCount > 0) {
            $days = (int) Carbon::parse((clone $late)->min('updated_at'))->diffInDays(now());
            $signals[] = ['key' => 'late-evidences', 'tone' => 'rejected',
                'title' => self::plural($lateCount, 'evidencia espera', 'evidencias esperan').' más de '.self::LATE_DAYS.' días',
                'detail' => 'La más antigua lleva '.self::plural($days, 'día', 'días').' en la cola',
                'action' => $nudge('COORDINATOR', 'Evidencias pendientes de revisar en '.$committee->name,
                    'Hay '.self::plural($lateCount, 'evidencia', 'evidencias').' de '.$committee->name.' esperando revisión desde hace más de '.self::LATE_DAYS.' días. ¿Puedes revisarlas cuanto antes?')];
        }

        $unminuted = MeetingRequest::where('committee_id', $committee->id)->whereNull('meeting_id')
            ->where('datetime', '<', now()->subDay())->orderBy('datetime');
        $unminutedCount = (clone $unminuted)->count();
        if ($unminutedCount > 0) {
            $oldest = (clone $unminuted)->first();
            $signals[] = ['key' => 'requests-without-minutes', 'tone' => 'pending',
                'title' => self::plural($unminutedCount, 'convocatoria celebrada', 'convocatorias celebradas').' sin acta',
                'detail' => 'La más antigua es «'.$oldest->title.'» del '.Carbon::parse($oldest->datetime)->format('d/m'),
                'action' => $nudge('SECRETARY', 'Acta pendiente de «'.$oldest->title.'»',
                    'La reunión de «'.$oldest->title.'» ya se celebró y todavía no tiene acta. ¿Puedes levantarla para que cuenten las horas de quienes asistieron?',
                    ['request' => $oldest->id])];
        }

        $waiting = $this->threads($committee)->open()->whereColumn('last_message_by', 'author_id')
            ->where('last_message_at', '<', now()->subDays(self::UNANSWERED_DAYS))->orderBy('last_message_at');
        $waitingCount = (clone $waiting)->count();
        if ($waitingCount > 0) {
            $oldest = (clone $waiting)->first();
            $role = $oldest->recipient_role === 'SECRETARY' ? 'SECRETARY' : 'COORDINATOR';
            $signals[] = ['key' => 'unanswered-messages', 'tone' => 'pending',
                'title' => self::plural($waitingCount, 'mensaje sin responder', 'mensajes sin responder'),
                'detail' => 'Llevan más de '.self::UNANSWERED_DAYS.' días esperando; el más antiguo es «'.$oldest->subject.'»',
                'action' => $nudge($role, 'Mensajes sin responder en '.$committee->name,
                    'Hay '.self::plural($waitingCount, 'mensaje', 'mensajes').' para '.$committee->name.' sin respuesta desde hace más de '.self::UNANSWERED_DAYS.' días. ¿Puedes contestarlos?')];
        }

        $lastMeeting = Meeting::where('committee_id', $committee->id)->where('datetime', '<=', now())->max('datetime');
        $quietDays = $lastMeeting === null ? null : (int) Carbon::parse($lastMeeting)->diffInDays(now());
        if ($lastMeeting === null || $quietDays > self::QUIET_DAYS) {
            $signals[] = ['key' => 'no-meetings', 'tone' => 'notice',
                'title' => $lastMeeting === null ? 'Sin reuniones todavía' : 'Sin reuniones desde hace '.self::plural($quietDays, 'día', 'días'),
                'detail' => 'Un comité activo se reúne al menos cada tres semanas',
                'action' => $nudge($secretaried ? 'SECRETARY' : 'COORDINATOR', 'Próxima reunión de '.$committee->name,
                    $committee->name.' no se reúne desde hace tiempo. ¿Tenéis prevista una convocatoria?')];
        }

        $rejected = Evidence::where('committee_id', $committee->id)->where('last', true)->where('status', 'REJECTED')
            ->where('updated_at', '<', now()->subDays(self::LATE_DAYS))->count();
        if ($rejected > 0) {
            $signals[] = ['key' => 'rejected-uncorrected', 'tone' => 'notice',
                'title' => self::plural($rejected, 'evidencia rechazada', 'evidencias rechazadas').' sin corregir',
                'detail' => 'Sus autores no han enviado una versión nueva en '.self::LATE_DAYS.' días',
                'action' => ['label' => 'Ver las rechazadas', 'icon' => 'list-checks',
                    'href' => route(self::prefix().'.evidence.list', ['estado' => 'rechazadas', 'comite' => $committee->id])]];
        }

        return $signals;
    }

    /**
     * Coordinators and secretaries of the committee with their last access, their last annotation in the committee
     * and, for a coordinator, their last review.
     *
     * @return list<array{user: User, role: string, audience: string, lastLogin: ?Carbon, lastAction: ?Carbon, lastReview: ?Carbon}>
     */
    private function team(Committee $committee, Collection $coordinators, Collection $secretaries): array
    {
        $people = $coordinators->map(fn (User $user): array => ['user' => $user, 'role' => 'COORDINATOR'])
            ->merge($secretaries->map(fn (User $user): array => ['user' => $user, 'role' => 'SECRETARY']));
        $ids = $people->pluck('user.id')->unique()->all();
        $at = fn ($value): ?Carbon => $value === null ? null : Carbon::parse($value);

        $logins = ActivityLog::whereIn('user_id', $ids)->where('action', ActivityAction::Login->value)
            ->groupBy('user_id')->selectRaw('user_id, max(created_at) as at')->pluck('at', 'user_id');
        $actions = ActivityLog::whereIn('user_id', $ids)->where('committee_id', $committee->id)
            ->whereIn('acting_role', ['COORDINATOR', 'SECRETARY'])
            ->groupBy('user_id')->selectRaw('user_id, max(created_at) as at')->pluck('at', 'user_id');
        $reviews = ActivityLog::whereIn('user_id', $ids)->where('committee_id', $committee->id)
            ->whereIn('action', [ActivityAction::EvidenceAccepted->value, ActivityAction::EvidenceRejected->value])
            ->groupBy('user_id')->selectRaw('user_id, max(created_at) as at')->pluck('at', 'user_id');

        return $people->map(fn (array $person): array => $person + [
            'audience' => $person['role'].':'.$committee->id,
            'lastLogin' => $at($logins->get($person['user']->id)),
            'lastAction' => $at($actions->get($person['user']->id)),
            'lastReview' => $person['role'] === 'COORDINATOR' ? $at($reviews->get($person['user']->id)) : null,
        ])->values()->all();
    }

    /** Hours the committee credits by origin: accepted evidences, its meetings and its bonuses. */
    private function origins(Committee $committee): array
    {
        $evidences = (float) Evidence::where('committee_id', $committee->id)->where('last', true)->where('status', 'ACCEPTED')->sum('hours');
        $meetings = (float) DB::table('meeting_user')->join('meetings', 'meetings.id', '=', 'meeting_user.meeting_id')
            ->where('meetings.committee_id', $committee->id)->sum('meetings.hours');
        $bonuses = (float) DB::table('bonus_user')->join('bonuses', 'bonuses.id', '=', 'bonus_user.bonus_id')
            ->where('bonuses.committee_id', $committee->id)->sum('bonuses.hours');

        $total = $evidences + $meetings + $bonuses;

        return [
            'total' => $total,
            'segments' => array_map(fn (array $origin): array => [
                'label' => $origin[0],
                'tone' => $origin[2],
                'value' => $total > 0 ? $origin[1] * 100 / $total : 0,
                'text' => number_format($origin[1], 2, ',', '.').' h',
            ], [['Evidencias', $evidences, 'accepted'], ['Reuniones', $meetings, 'notice'], ['Bonos', $bonuses, 'draft']]),
        ];
    }

    /** Threads addressed to the coordination or the secretary of the committee. */
    private function threads(Committee $committee)
    {
        return MessageThread::query()->where('committee_id', $committee->id)->whereIn('recipient_role', ['COORDINATOR', 'SECRETARY']);
    }

    /** The users list, where the management assigns the coordination and the secretary. */
    private function assign(): array
    {
        return ['label' => 'Asignar en Alumnos', 'href' => route(self::prefix().'.user.list'), 'icon' => 'user-cog'];
    }

    private static function prefix(): string
    {
        return auth()->user()?->hasRole('LECTURE') ? 'lecture' : 'president';
    }

    private static function plural(int $count, string $one, string $many): string
    {
        return $count.' '.($count === 1 ? $one : $many);
    }
}
