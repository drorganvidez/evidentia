<?php

namespace App\View;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Data of the home page of the signed-in person: their hours and evidences when they take part in the course, the
 * review queue of a coordinator, the figures and the attention queue of the course for the president and the
 * lecturer, and what everybody sees (deadlines, next meetings, the documents of each committee and the events).
 */
final class Dashboard
{
    private const PARTICIPANTS = ['STUDENT', 'COORDINATOR', 'SECRETARY', 'REGISTER_COORDINATOR'];

    /**
     * @return array<string, mixed>
     */
    public function for(User $user): array
    {
        $roles = $user->roles->pluck('rol')->all();
        $config = Configuration::find(1);
        $uploadClosesAt = $config?->upload_evidences_timestamp;

        return [
            'name' => (string) $user->name,
            'upload' => $this->open($uploadClosesAt) ? Carbon::parse($uploadClosesAt) : null,
            'mine' => array_intersect($roles, self::PARTICIPANTS) !== [] ? $this->mine($user) : null,
            'review' => in_array('COORDINATOR', $roles, true) ? $this->review($user, $config) : null,
            'management' => array_intersect($roles, ['PRESIDENT', 'LECTURE']) !== []
                ? $this->management(in_array('LECTURE', $roles, true) ? 'lecture' : 'president', $config)
                : null,
            'deadlines' => $this->deadlines($config),
            'meetings' => MeetingRequest::nextMeetingRequests()->load('committee'),
            'committees' => Committee::with(['secretaries.meetingRequests', 'secretaries.meetingMinutes.meeting'])->orderBy('name')->get(),
            'events' => Event::where('hidden', false)->orderBy('start_datetime')->get(),
        ];
    }

    /**
     * The computed hours of a person and their share by origin, as the segments of x-ui.meter.
     *
     * @return array{total: float, segments: list<array{value: int, tone: string, label: string, text: string}>}
     */
    public function hours(User $user): array
    {
        $hours = [
            ['label' => 'Evidencias', 'tone' => 'accepted', 'hours' => (float) $user->evidences_accepted_hours()],
            ['label' => 'Reuniones', 'tone' => 'notice', 'hours' => (float) $user->meetings_hours()],
            ['label' => 'Eventos', 'tone' => 'pending', 'hours' => (float) $user->events_hours()],
            ['label' => 'Bonos', 'tone' => 'draft', 'hours' => (float) $user->bonus_hours()],
        ];
        $shares = $this->percentages(array_column($hours, 'hours'));

        return [
            'total' => array_sum(array_column($hours, 'hours')),
            'segments' => array_map(fn (array $part, int $share): array => [
                'value' => $share,
                'tone' => $part['tone'],
                'label' => $part['label'],
                'text' => number_format($part['hours'], 2, ',', '.').' h',
            ], $hours, $shares),
        ];
    }

    /** The hours of the person by origin and their evidences by status. */
    private function mine(User $user): array
    {
        $latest = $user->evidences->where('last', true);

        return $this->hours($user) + [
            'pending' => $latest->where('status', 'PENDING'),
            'rejected' => $latest->where('status', 'REJECTED'),
            'drafts' => $latest->where('status', 'DRAFT'),
            'accepted' => $latest->where('status', 'ACCEPTED'),
            'latest' => $latest->load('committee')->sortByDesc('updated_at')->take(5)->values(),
            'count' => $latest->count(),
        ];
    }

    /** The evidences of the committee of a coordinator waiting for a review, never their own. */
    private function review(User $user, ?Configuration $config): ?array
    {
        $committee = $user->coordinator?->committee;

        if ($committee === null) {
            return null;
        }

        $pending = $committee->evidencesPending()->where('user_id', '!=', $user->id)->with('user')->get();
        $closesAt = $config?->validate_evidences_timestamp;

        return [
            'committee' => $committee,
            'count' => $pending->count(),
            'items' => $pending->take(5)->values(),
            'closesAt' => $this->open($closesAt) ? Carbon::parse($closesAt) : null,
        ];
    }

    /** Figures of the course, what needs attention, the committees and the latest activity. */
    private function management(string $prefix, ?Configuration $config): array
    {
        $byStatus = Evidence::where('last', true)->where('status', '!=', 'DRAFT')
            ->selectRaw('status, count(*) as total, coalesce(sum(hours), 0) as hours')
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        $count = fn (string $status): int => (int) ($byStatus[$status]->total ?? 0);
        $statuses = [
            ['label' => 'Aceptadas', 'tone' => 'accepted', 'total' => $count('ACCEPTED')],
            ['label' => 'Pendientes', 'tone' => 'pending', 'total' => $count('PENDING')],
            ['label' => 'Rechazadas', 'tone' => 'rejected', 'total' => $count('REJECTED')],
        ];
        $shares = $this->percentages(array_column($statuses, 'total'));
        $total = array_sum(array_column($statuses, 'total'));

        $committees = Committee::withCount([
            'coordinators', 'secretaries',
            'evidences as pending_count' => fn ($query) => $query->where('last', true)->where('status', 'PENDING'),
            'evidences as evidences_count' => fn ($query) => $query->where('last', true)->where('status', '!=', 'DRAFT'),
        ])->orderBy('name')->get();

        $routes = [
            'evidences' => route("$prefix.evidence.list"),
            'users' => route("$prefix.user.list"),
            'meetings' => route("$prefix.meeting.list"),
            'committees' => route("$prefix.committee.list"),
        ];

        $validateClosesAt = $config?->validate_evidences_timestamp;
        $attention = [];
        if ($count('PENDING') > 0) {
            $closing = $this->open($validateClosesAt) && Carbon::parse($validateClosesAt)->lte(now()->addDays(7));
            $attention[] = [
                'label' => 'Evidencias pendientes de revisión',
                'value' => $count('PENDING'),
                'tone' => $closing ? 'rejected' : 'pending',
                'text' => $closing ? 'La validación cierra pronto' : null,
                'closesAt' => $closing ? Carbon::parse($validateClosesAt) : null,
                'href' => $routes['evidences'],
            ];
        }
        foreach ([['coordinators_count', 'Comités sin coordinador'], ['secretaries_count', 'Comités sin secretario']] as [$column, $label]) {
            $missing = $committees->where($column, 0);
            if ($missing->isNotEmpty()) {
                $attention[] = [
                    'label' => $label,
                    'value' => $missing->count(),
                    'tone' => 'notice',
                    'text' => $missing->pluck('name')->implode(', '),
                    'closesAt' => null,
                    'href' => $routes['committees'],
                ];
            }
        }

        $students = Role::where('rol', 'STUDENT')->first();

        return [
            'routes' => $routes,
            'total' => $total,
            'hours' => (float) ($byStatus['ACCEPTED']->hours ?? 0),
            'statuses' => array_map(fn (array $status, int $share): array => $status + ['value' => $share], $statuses, $shares),
            'pending' => $count('PENDING'),
            'students' => $students?->users()->count() ?? 0,
            'meetings' => Meeting::count(),
            'committees' => $committees,
            'attention' => $attention,
            'activity' => Evidence::where('last', true)->where('status', '!=', 'DRAFT')
                ->with(['user', 'committee'])->orderByDesc('updated_at')->limit(6)->get(),
        ];
    }

    /** Every deadline of the course that has a date, open or closed. */
    private function deadlines(?Configuration $config): Collection
    {
        if ($config === null) {
            return collect();
        }

        return collect([
            ['label' => 'Subida de evidencias', 'at' => $config->upload_evidences_timestamp],
            ['label' => 'Validación de evidencias', 'at' => $config->validate_evidences_timestamp],
            ['label' => 'Registro de reuniones', 'at' => $config->meetings_timestamp],
            ['label' => 'Registro de bonos', 'at' => $config->bonus_timestamp],
            ['label' => 'Eventos y asistencias', 'at' => $config->attendee_timestamp],
        ])
            ->filter(fn (array $deadline): bool => $deadline['at'] !== null)
            ->map(fn (array $deadline): array => [
                'label' => $deadline['label'],
                'at' => Carbon::parse($deadline['at']),
                'open' => $this->open($deadline['at']),
            ])
            ->values();
    }

    private function open(mixed $closesAt): bool
    {
        return $closesAt !== null && Carbon::now()->lt(Carbon::parse($closesAt));
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
