<?php

namespace App\View;

use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\ErrorReport;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use App\Services\ReviewQueue;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Control panel of the course for the president and the lecturer: the figures that matter (hours, participation,
 * review queue, acceptance), how the hours spread among the students and by origin, each committee at a glance,
 * the submissions week by week, the queue of the presidency (App\Services\ReviewQueue) and the traceability of the
 * activity log (latest actions, its signals, the accesses) with the open server errors.
 */
final class ManagementDashboard
{
    private const WEEKS = 8;

    /** Hours buckets of the distribution: [label, from (inclusive), to (exclusive)]. */
    private const BUCKETS = [
        ['Sin horas', 0, 0.0001],
        ['Menos de 5 h', 0.0001, 5],
        ['De 5 a 10 h', 5, 10],
        ['De 10 a 15 h', 10, 15],
        ['De 15 a 20 h', 15, 20],
        ['20 h o más', 20, INF],
    ];

    public function __construct(private Dashboard $dashboard, private ReviewQueue $queue) {}

    /** @return array<string, mixed> */
    public function for(User $user): array
    {
        $roles = $user->roles->pluck('rol')->all();
        $prefix = in_array('LECTURE', $roles, true) ? 'lecture' : 'president';
        $students = $this->students();
        $hours = $this->hoursByStudent($students);
        $byStatus = $this->evidencesByStatus();
        $pendingOldest = Evidence::where('last', true)->where('status', 'PENDING')->min('updated_at');
        $decided = $byStatus['ACCEPTED'] + $byStatus['REJECTED'];
        $participating = Evidence::where('status', '!=', 'DRAFT')->whereIn('user_id', $students)->distinct()->count('user_id');
        $presidency = $this->queue->forPresidency()->where('status', 'PENDING')->where('user_id', '!=', $user->id);

        return [
            'name' => (string) $user->name,
            'lecturer' => $prefix === 'lecture',
            'routes' => [
                // the presidents review their queue; the lecturer, the whole course
                'review' => in_array('PRESIDENT', $roles, true) ? route('president.review.pending') : route("$prefix.evidence.list"),
                'evidences' => route("$prefix.evidence.list"),
                'users' => route("$prefix.user.list"),
                'meetings' => route("$prefix.meeting.list"),
                'committees' => route("$prefix.committee.list"),
                'committee' => route("$prefix.committee.view", ['id' => '__id__']),
                'export' => route("$prefix.export"),
                // Not «errors»: views share a validation bag under that name
                'serverErrors' => route("$prefix.errors"),
            ],
            'kpis' => [
                'hours' => $hours['total'],
                'average' => $students->isEmpty() ? 0.0 : $hours['total'] / $students->count(),
                'students' => $students->count(),
                'participating' => $participating,
                'participation' => $students->isEmpty() ? 0 : (int) round($participating * 100 / $students->count()),
                'pending' => $byStatus['PENDING'],
                'pendingDays' => $pendingOldest === null ? null : (int) Carbon::parse($pendingOldest)->diffInDays(now()),
                'acceptance' => $decided === 0 ? null : (int) round($byStatus['ACCEPTED'] * 100 / $decided),
                'rejected' => $byStatus['REJECTED'],
            ],
            'origins' => $hours['origins'],
            'distribution' => $this->distribution($hours['perStudent'], $students->count()),
            'committees' => $this->committees(),
            'weeks' => $this->weeks(),
            'presidency' => [
                'count' => (clone $presidency)->count(),
                'items' => (clone $presidency)->with(['user', 'committee'])->orderBy('updated_at')->orderBy('id')->limit(5)->get(),
                'href' => route('president.review.pending'),
            ],
            'activity' => $this->activity(),
            'signals' => $this->signals(),
            'access' => $this->access(),
            // Not «errors»: views share a validation bag under that name
            'serverErrors' => [
                'open' => ErrorReport::open()->count(),
                'latest' => ErrorReport::open()->orderByDesc('last_seen_at')->limit(3)->get(),
                'show' => "$prefix.errors.show",
            ],
            'deadlines' => $this->dashboard->for($user)['deadlines'],
        ];
    }

    /** Ids of the people with the student role. */
    private function students(): Collection
    {
        $role = Role::where('rol', 'STUDENT')->first();

        return $role === null ? collect() : $role->users()->pluck('users.id');
    }

    /** @return array{ACCEPTED: int, PENDING: int, REJECTED: int, DRAFT: int} */
    private function evidencesByStatus(): array
    {
        $counts = Evidence::where('last', true)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'ACCEPTED' => (int) ($counts['ACCEPTED'] ?? 0),
            'PENDING' => (int) ($counts['PENDING'] ?? 0),
            'REJECTED' => (int) ($counts['REJECTED'] ?? 0),
            'DRAFT' => (int) ($counts['DRAFT'] ?? 0),
        ];
    }

    /** Accredited hours of every student by origin, the totals and the share of each origin. */
    private function hoursByStudent(Collection $students): array
    {
        $sources = [
            // the coauthors of an accepted evidence get its full hours as well
            'evidences' => Evidence::where('last', true)->where('status', 'ACCEPTED')->select('user_id', 'hours')
                ->unionAll(DB::table('evidence_coauthors')->join('evidences', 'evidences.id', '=', 'evidence_coauthors.evidence_id')
                    ->where('evidences.last', true)->where('evidences.status', 'ACCEPTED')
                    ->select('evidence_coauthors.user_id', 'evidences.hours'))
                ->get()->groupBy('user_id')->map(fn (Collection $rows): float => (float) $rows->sum('hours')),
            'meetings' => DB::table('meeting_user')->join('meetings', 'meetings.id', '=', 'meeting_user.meeting_id')
                ->groupBy('meeting_user.user_id')->selectRaw('meeting_user.user_id as user_id, sum(meetings.hours) as hours')->pluck('hours', 'user_id'),
            // of the events that share a group only the longest one counts (Event::countedHours)
            'events' => Event::query()->join('attendees', 'events.id', '=', 'attendees.event_id')
                ->where('attendees.status', 'Checked In')
                ->get(['events.id', 'events.hours', 'events.event_group', 'attendees.user_id'])
                ->groupBy('user_id')->map(fn (Collection $events): float => Event::countedHours($events)),
            'bonuses' => DB::table('bonus_user')->join('bonuses', 'bonuses.id', '=', 'bonus_user.bonus_id')
                ->groupBy('bonus_user.user_id')->selectRaw('bonus_user.user_id as user_id, sum(bonuses.hours) as hours')->pluck('hours', 'user_id'),
        ];

        $perStudent = $students->mapWithKeys(fn (int $id) => [
            $id => array_sum(array_map(fn (Collection $source): float => (float) ($source[$id] ?? 0), $sources)),
        ]);
        $byOrigin = array_map(
            fn (Collection $source): float => (float) $source->only($students->all())->sum(),
            $sources,
        );
        $total = array_sum($byOrigin);
        $labels = ['evidences' => ['Evidencias', 'accepted'], 'meetings' => ['Reuniones', 'notice'], 'events' => ['Asistencia', 'pending'], 'bonuses' => ['Bonos', 'draft']];
        $shares = $this->percentages(array_values($byOrigin));

        return [
            'total' => $total,
            'perStudent' => $perStudent,
            'origins' => array_values(array_map(
                fn (string $key, int $share): array => [
                    'label' => $labels[$key][0],
                    'tone' => $labels[$key][1],
                    'value' => $share,
                    'text' => number_format($byOrigin[$key], 2, ',', '.').' h',
                ],
                array_keys($byOrigin),
                $shares,
            )),
        ];
    }

    /** How many students fall in each band of accredited hours. */
    private function distribution(Collection $perStudent, int $students): array
    {
        return array_map(function (array $bucket) use ($perStudent, $students): array {
            [$label, $from, $to] = $bucket;
            $count = $perStudent->filter(fn (float $hours): bool => $hours >= $from && $hours < $to)->count();

            return [
                'label' => $label,
                'value' => $count,
                'text' => $students === 0 ? null : round($count * 100 / $students).' %',
                'tone' => $from === 0 ? 'draft' : null,
            ];
        }, self::BUCKETS);
    }

    /** Each committee with its coordination (the presidents in the committee of the presidency), the state of its evidences and its oldest pending one. */
    private function committees(): Collection
    {
        $presidents = $this->queue->presidents()->map(fn (User $user): string => trim($user->name.' '.$user->surname))->implode(', ');

        return Committee::with(['coordinators.user', 'secretaries.user'])
            ->withCount([
                'evidences as accepted_count' => fn ($query) => $query->where('last', true)->where('status', 'ACCEPTED'),
                'evidences as pending_count' => fn ($query) => $query->where('last', true)->where('status', 'PENDING'),
                'evidences as rejected_count' => fn ($query) => $query->where('last', true)->where('status', 'REJECTED'),
            ])
            ->withMin(['evidences as oldest_pending' => fn ($query) => $query->where('last', true)->where('status', 'PENDING')], 'updated_at')
            ->orderBy('name')
            ->get()
            ->map(function (Committee $committee) use ($presidents): array {
                $counts = [$committee->accepted_count, $committee->pending_count, $committee->rejected_count];
                $coordinators = $committee->coordinators->map(fn ($coordinator) => trim($coordinator->user?->name.' '.$coordinator->user?->surname))->filter();
                if ($this->queue->isPresidency($committee) && $presidents !== '') {
                    $coordinators->prepend($presidents);
                }
                $shares = $this->percentages($counts);

                return [
                    'id' => $committee->id,
                    'name' => $committee->name,
                    'coordinators' => $coordinators->implode(', '),
                    'secretaries' => $committee->secretaries->count(),
                    'total' => array_sum($counts),
                    'pending' => $committee->pending_count,
                    'oldest' => $committee->oldest_pending ? Carbon::parse($committee->oldest_pending) : null,
                    'segments' => [
                        ['label' => 'Aceptadas', 'tone' => 'accepted', 'value' => $shares[0], 'text' => (string) $counts[0]],
                        ['label' => 'Pendientes', 'tone' => 'pending', 'value' => $shares[1], 'text' => (string) $counts[1]],
                        ['label' => 'Rechazadas', 'tone' => 'rejected', 'value' => $shares[2], 'text' => (string) $counts[2]],
                    ],
                ];
            });
    }

    /** Evidences sent (every version that left the draft) in each of the last weeks, the current one last. */
    private function weeks(): array
    {
        $start = now()->startOfWeek()->subWeeks(self::WEEKS - 1);
        $dates = Evidence::where('status', '!=', 'DRAFT')->where('created_at', '>=', $start)->pluck('created_at');

        return collect(range(0, self::WEEKS - 1))->map(function (int $offset) use ($start, $dates): array {
            $from = $start->copy()->addWeeks($offset);
            $to = $from->copy()->addWeek();

            return [
                'label' => $offset === self::WEEKS - 1 ? 'Esta semana' : 'Semana del '.$from->format('d/m'),
                'value' => $dates->filter(fn ($date): bool => Carbon::parse($date)->gte($from) && Carbon::parse($date)->lt($to))->count(),
            ];
        })->all();
    }

    /** The latest actions of the log, without the accesses and the notices. */
    private function activity(): Collection
    {
        return ActivityLog::query()->withoutNoise()->whereNotIn('action', ['login', 'login_failed', 'logout', 'notification.sent'])
            ->with('user')->orderByDesc('id')->limit(10)->get();
    }

    /** Signals of the log in the last 30 days: blocked self reviews, actions after a deadline and double roles. */
    private function signals(): array
    {
        $since = now()->subDays(30);
        $flags = ActivityLog::where('created_at', '>=', $since)->whereNotNull('flags')->pluck('flags')
            ->flatMap(fn ($flags) => (array) $flags)
            ->countBy();

        return [
            ['label' => 'Autorrevisiones', 'value' => (int) ($flags['self_review'] ?? 0), 'tone' => 'rejected'],
            ['label' => 'Fuera de plazo', 'value' => (int) ($flags['after_deadline'] ?? 0), 'tone' => 'pending'],
            ['label' => 'Doble rol', 'value' => (int) ($flags['role_overlap'] ?? 0), 'tone' => 'notice'],
        ];
    }

    /** Sign-ins and failed sign-ins of the last seven days and the people who signed in. */
    private function access(): array
    {
        $since = now()->subDays(7);

        return [
            'logins' => ActivityLog::where('action', 'login')->where('created_at', '>=', $since)->count(),
            'people' => ActivityLog::where('action', 'login')->where('created_at', '>=', $since)->distinct()->count('user_id'),
            'failed' => ActivityLog::where('action', 'login_failed')->where('created_at', '>=', $since)->count(),
        ];
    }

    /**
     * Whole percentages that add up to 100 (largest remainder), or zeros when there is nothing to share.
     *
     * @param  list<float|int>  $values
     * @return list<int>
     */
    private function percentages(array $values): array
    {
        $sum = array_sum($values);
        if ($sum <= 0) {
            return array_fill(0, count($values), 0);
        }

        $exact = array_map(fn ($value): float => $value * 100 / $sum, $values);
        $shares = array_map(fn (float $value): int => (int) floor($value), $exact);
        $remainders = array_map(fn (float $value, int $share): float => $value - $share, $exact, $shares);
        arsort($remainders);
        foreach (array_slice(array_keys($remainders), 0, 100 - array_sum($shares)) as $index) {
            $shares[$index]++;
        }

        return $shares;
    }
}
