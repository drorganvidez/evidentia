<?php

namespace App\View;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Workspace of a committee for its coordinator: the review queue, the people who take part in it (evidences sent to
 * it, its meetings and its bonuses) with the hours each one gets from it, how those hours spread, the submissions
 * week by week, its meetings and the latest annotations of the activity log about it. The coordinator's own
 * evidences count like anybody else's, but the queue leaves them out because somebody else reviews them.
 */
final class CommitteePanel
{
    private const WEEKS = 8;

    private const TOP = 10;

    private const STATES = ['PENDING', 'ACCEPTED', 'REJECTED'];

    /** @return array<string, mixed> */
    public function for(Committee $committee, User $viewer): array
    {
        $members = $this->members($committee);
        $queue = $committee->evidences()->where('last', true)->where('status', 'PENDING')->where('user_id', '!=', $viewer->id);
        $oldest = (clone $queue)->min('updated_at');
        $accepted = $committee->evidences()->where('last', true)->where('status', 'ACCEPTED');

        $committee->loadMissing(['coordinators.user', 'secretaries.user']);

        return [
            'committee' => $committee,
            'coordinators' => $committee->coordinators->pluck('user')->filter()->values(),
            'secretaries' => $committee->secretaries->pluck('user')->filter()->values(),
            'kpis' => [
                'pending' => $queue->count(),
                'pendingDays' => $oldest === null ? null : (int) Carbon::parse($oldest)->diffInDays(now()),
                'acceptedHours' => (float) (clone $accepted)->sum('hours'),
                'accepted' => $accepted->count(),
                'hours' => (float) $members->sum('hours'),
                'rejected' => $committee->evidences()->where('last', true)->where('status', 'REJECTED')->count(),
                'members' => $members->count(),
            ],
            'members' => $members,
            'distribution' => $this->distribution($members),
            'weeks' => $this->weeks($committee),
            'requests' => MeetingRequest::where('committee_id', $committee->id)->where('datetime', '>=', now())
                ->orderBy('datetime')->limit(5)->get(),
            'minutes' => MeetingMinutes::query()
                ->join('meetings', 'meetings.id', '=', 'meeting_minutes.meeting_id')
                ->where('meetings.committee_id', $committee->id)
                ->orderByDesc('meetings.datetime')->orderByDesc('meeting_minutes.id')
                ->select('meeting_minutes.*')->with('meeting')->limit(5)->get(),
            'activity' => ActivityLog::query()->inCommittees([$committee->id])->withoutNoise()
                ->whereNotIn('action', ActivityAction::inGroup('notifications'))
                ->with('user')->orderByDesc('id')->limit(10)->get(),
        ];
    }

    /**
     * Everybody with an evidence sent to the committee, at one of its meetings or with one of its bonuses, by surname:
     * their evidences by state, the hours the committee credits them, the meetings and their latest activity.
     *
     * @return Collection<int, array{id: int, name: string, surname: string, username: string, email: string, accepted: int, pending: int, rejected: int, hours: float, meetings: int, last: ?Carbon}>
     */
    public function members(Committee $committee): Collection
    {
        $evidences = Evidence::where('committee_id', $committee->id)->where('last', true)->whereIn('status', self::STATES)
            ->groupBy('user_id')
            ->selectRaw("user_id,
                sum(case when status = 'ACCEPTED' then 1 else 0 end) as accepted,
                sum(case when status = 'PENDING' then 1 else 0 end) as pending,
                sum(case when status = 'REJECTED' then 1 else 0 end) as rejected,
                sum(case when status = 'ACCEPTED' then hours else 0 end) as hours,
                max(updated_at) as last_at")
            ->get()->keyBy('user_id');
        $meetings = DB::table('meeting_user')->join('meetings', 'meetings.id', '=', 'meeting_user.meeting_id')
            ->where('meetings.committee_id', $committee->id)
            ->groupBy('meeting_user.user_id')
            ->selectRaw('meeting_user.user_id as user_id, count(distinct meetings.id) as meetings, sum(meetings.hours) as hours, max(meetings.datetime) as last_at')
            ->get()->keyBy('user_id');
        $bonuses = DB::table('bonus_user')->join('bonuses', 'bonuses.id', '=', 'bonus_user.bonus_id')
            ->where('bonuses.committee_id', $committee->id)
            ->groupBy('bonus_user.user_id')
            ->selectRaw('bonus_user.user_id as user_id, sum(bonuses.hours) as hours, max(bonuses.created_at) as last_at')
            ->get()->keyBy('user_id');

        $ids = $evidences->keys()->merge($meetings->keys())->merge($bonuses->keys())->unique()->all();

        return User::whereIn('id', $ids)->orderBy('surname')->orderBy('name')->get()
            ->map(function (User $user) use ($evidences, $meetings, $bonuses): array {
                $evidence = $evidences->get($user->id);
                $meeting = $meetings->get($user->id);
                $bonus = $bonuses->get($user->id);
                $last = collect([$evidence?->last_at, $meeting?->last_at, $bonus?->last_at])
                    ->filter()->map(fn ($date): Carbon => Carbon::parse($date))->max();

                return [
                    'id' => (int) $user->id,
                    'name' => (string) $user->name,
                    'surname' => (string) $user->surname,
                    'username' => (string) $user->username,
                    'email' => (string) $user->email,
                    'accepted' => (int) ($evidence->accepted ?? 0),
                    'pending' => (int) ($evidence->pending ?? 0),
                    'rejected' => (int) ($evidence->rejected ?? 0),
                    'hours' => (float) ($evidence->hours ?? 0) + (float) ($meeting->hours ?? 0) + (float) ($bonus->hours ?? 0),
                    'meetings' => (int) ($meeting->meetings ?? 0),
                    'last' => $last,
                ];
            })
            ->values();
    }

    /**
     * The members as a spreadsheet: UTF-8 with BOM, semicolons, Spanish decimal comma, one row per member. A value that
     * a spreadsheet would run as a formula starts with a single quote.
     */
    public function csv(Collection $members): string
    {
        $rows = [['nombre', 'apellidos', 'uvus', 'correo', 'aceptadas', 'pendientes', 'rechazadas', 'horas', 'reuniones']];
        foreach ($members as $member) {
            $rows[] = [
                $member['name'], $member['surname'], $member['username'], $member['email'],
                $member['accepted'], $member['pending'], $member['rejected'],
                number_format($member['hours'], 2, ',', ''), $member['meetings'],
            ];
        }

        $stream = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($stream, array_map(fn ($value): string => self::cell((string) $value), $row), ';', '"', '');
        }
        rewind($stream);
        $csv = (string) stream_get_contents($stream);
        fclose($stream);

        return "\u{FEFF}".$csv;
    }

    /** A value that starts like a formula (= + - @, or a tab or carriage return before one) is kept as text. */
    public static function cell(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'".$value : $value;
    }

    /** The members with the most hours credited by the committee, the first ten. */
    private function distribution(Collection $members): array
    {
        return $members->filter(fn (array $member): bool => $member['hours'] > 0)
            ->sortBy([['hours', 'desc'], ['surname', 'asc'], ['name', 'asc']])
            ->take(self::TOP)
            ->map(fn (array $member): array => [
                'label' => trim($member['name'].' '.$member['surname']),
                'value' => $member['hours'],
            ])
            ->values()->all();
    }

    /** Evidences sent to the committee (every version that left the draft) in each of the last weeks, the current one last. */
    private function weeks(Committee $committee): array
    {
        $start = now()->startOfWeek()->subWeeks(self::WEEKS - 1);
        $dates = Evidence::where('committee_id', $committee->id)->whereIn('status', self::STATES)
            ->where('created_at', '>=', $start)->pluck('created_at');

        return collect(range(0, self::WEEKS - 1))->map(function (int $offset) use ($start, $dates): array {
            $from = $start->copy()->addWeeks($offset);
            $to = $from->copy()->addWeek();

            return [
                'label' => $offset === self::WEEKS - 1 ? 'Esta semana' : 'Semana del '.$from->format('d/m'),
                'value' => $dates->filter(fn ($date): bool => Carbon::parse($date)->gte($from) && Carbon::parse($date)->lt($to))->count(),
            ];
        })->all();
    }
}
