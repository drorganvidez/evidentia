<?php

namespace App\View;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Attendee;
use App\Models\Bonus;
use App\Models\Committee;
use App\Models\Coordinator;
use App\Models\DefaultList;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\MessageThread;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\User;
use App\Services\Activity\ActivityExport;
use App\Services\Activity\ActivityPresenter;
use App\Support\Roles;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The whole record of a person for the president and the lecturers, so that they can evaluate it: their account and
 * accesses, the committees they run, the hours by origin, and every evidence they sent, meeting they attended, sheet
 * they signed (and request of their committees they were expected at), attendance, bonus, annotation of the activity
 * log and message they wrote, the newest first. The same data leaves as a CSV for a spreadsheet.
 */
final class StudentRecord
{
    /** Sections of the record: key of the query string => label of its tab */
    public const TABS = [
        'evidencias' => 'Evidencias',
        'reuniones' => 'Reuniones',
        'firmas' => 'Firmas',
        'asistencias' => 'Asistencias',
        'bonos' => 'Bonos',
        'actividad' => 'Actividad',
        'mensajes' => 'Mensajes',
    ];

    /** Views of the activity: key of the query string => label */
    public const SCOPES = [
        'todo' => 'Todo',
        'hecho' => 'Lo que ha hecho',
        'afecta' => 'Lo que le afecta',
        'accesos' => 'Accesos',
    ];

    public const PAGE_SIZE = 30;

    public const CSV_HEADERS = ['seccion', 'fecha', 'concepto', 'comite', 'estado', 'horas', 'detalle'];

    /** Literals of Eventbrite in the words of x-ui.attendee-status */
    private const ATTENDANCE = [
        'Attending' => 'Pendiente de asistir',
        'Checked In' => 'Asistido',
        'Guests Attended' => 'Asistido (invitado)',
        'Guests Attending' => 'Pendiente de asistir (invitado)',
        'Not Attending (Refunded/Canceled)' => 'Asistencia cancelada',
        'Not Attending' => 'No asistido',
    ];

    public function __construct(private Dashboard $dashboard, private ActivityPresenter $presenter) {}

    /** The section asked for in the query string, or the first one for anything else (an array included). */
    public static function tab(mixed $tab): string
    {
        return is_string($tab) && array_key_exists($tab, self::TABS) ? $tab : array_key_first(self::TABS);
    }

    public static function scope(mixed $scope): string
    {
        return is_string($scope) && array_key_exists($scope, self::SCOPES) ? $scope : array_key_first(self::SCOPES);
    }

    /** @return array<string, mixed> */
    public function for(User $user, User $viewer, mixed $tab = null, mixed $scope = null): array
    {
        $tab = self::tab($tab);
        $scope = self::scope($scope);
        $evidences = $this->evidences($user);
        $meetings = $this->meetings($user);
        $signatures = $this->signatures($user);
        $attendances = $this->attendances($user);
        $bonuses = $this->bonuses($user);
        $threads = $this->threads($user);

        return [
            'user' => $user,
            'prefix' => $viewer->hasRole('LECTURE') ? 'lecture' : 'president',
            'tab' => $tab,
            'scope' => $scope,
            'hours' => $this->dashboard->hours($user),
            'account' => $this->account($user),
            'counts' => [
                'evidencias' => $evidences->count(),
                'reuniones' => $meetings->count(),
                'firmas' => $signatures->count(),
                'asistencias' => $attendances->count(),
                'bonos' => $bonuses->count(),
                'actividad' => $this->activityQuery($user, 'todo')->count(),
                'mensajes' => $threads->count(),
            ],
            'evidences' => $evidences,
            'meetings' => $meetings,
            'signatures' => $signatures,
            'requests' => $tab === 'firmas' ? $this->expectedRequests($user) : collect(),
            'attendances' => $attendances,
            'bonuses' => $bonuses,
            'threads' => $threads,
            'activity' => $tab === 'actividad' ? $this->activity($user, $scope) : null,
            'scopeCounts' => $tab === 'actividad'
                ? collect(array_keys(self::SCOPES))->mapWithKeys(fn (string $key) => [$key => $this->activityQuery($user, $key)->count()])->all()
                : [],
            'totals' => [
                'evidences' => [
                    'accepted' => $evidences->where('status', 'ACCEPTED')->count(),
                    'pending' => $evidences->where('status', 'PENDING')->count(),
                    'rejected' => $evidences->where('status', 'REJECTED')->count(),
                    'acceptedHours' => (float) $evidences->where('status', 'ACCEPTED')->sum('hours'),
                    'hours' => (float) $evidences->sum('hours'),
                ],
                'meetings' => (float) $meetings->sum('hours'),
                'attendances' => [
                    'attended' => $attendances->where('status', Attendee::ATTENDED)->count(),
                    'hours' => (float) $attendances->sum(fn (Attendee $attendee): float => $attendee->computedHours()),
                ],
                'bonuses' => (float) $bonuses->sum('hours'),
                'messages' => (int) $threads->sum('written_count'),
            ],
        ];
    }

    /**
     * Username, email, block, the committees the person runs and the default lists they are in, and their accesses:
     * the last sign-in, how many and their last action.
     *
     * @return array<string, mixed>
     */
    public function account(User $user): array
    {
        $access = ActivityAction::inGroup('access');
        $at = fn ($value): ?CarbonInterface => $value === null ? null : Carbon::parse($value);
        $logins = ActivityLog::where('user_id', $user->id)->where('action', ActivityAction::Login->value);

        return [
            'username' => (string) $user->username,
            'email' => (string) $user->email,
            'blocked' => (bool) $user->block,
            'committees' => $this->committees($user),
            'lists' => DefaultList::whereHas('users', fn (Builder $query) => $query->whereKey($user->id))
                ->with('secretary.committee')->orderBy('name')->get(),
            'lastLogin' => $at((clone $logins)->max('created_at')),
            'logins' => (clone $logins)->count(),
            'lastAction' => $at(ActivityLog::where('user_id', $user->id)
                ->whereNotIn('action', [...$access, ActivityAction::NotificationSent->value])->max('created_at')),
        ];
    }

    /**
     * Committees the person coordinates or is the secretary of, while they keep the role.
     *
     * @return Collection<int, array{committee: Committee, role: string}>
     */
    public function committees(User $user): Collection
    {
        $coordinates = $user->hasRole('COORDINATOR')
            ? Coordinator::with('committee')->where('user_id', $user->id)->get()->pluck('committee')->filter()
                ->map(fn ($committee): array => ['committee' => $committee, 'role' => 'COORDINATOR'])
            : collect();
        $secretary = $user->hasRole('SECRETARY')
            ? Secretary::with('committee')->where('user_id', $user->id)->get()->pluck('committee')->filter()
                ->map(fn ($committee): array => ['committee' => $committee, 'role' => 'SECRETARY'])
            : collect();

        return $coordinates->concat($secretary)->sortBy(fn (array $item): string => $item['committee']->name)->values();
    }

    /**
     * The last version of every evidence that left the draft, with its version number.
     *
     * @return Collection<int, Evidence>
     */
    public function evidences(User $user): Collection
    {
        $chain = Evidence::where('user_id', $user->id)->pluck('points_to', 'id');

        return Evidence::where('user_id', $user->id)->where('last', true)->where('status', '!=', 'DRAFT')
            ->with(['committee', 'reasonRejection'])
            ->orderByDesc('updated_at')->orderByDesc('id')->get()
            ->each(fn (Evidence $evidence) => $evidence->setAttribute('version', $this->version($evidence->id, $chain)));
    }

    /** @return Collection<int, Meeting> */
    public function meetings(User $user): Collection
    {
        return $user->meetings()->with(['committee', 'meetingMinutes'])
            ->orderByDesc('meetings.datetime')->orderByDesc('meetings.id')->get();
    }

    /**
     * The sheets the person signed, with when they signed (signed_at).
     *
     * @return Collection<int, SignatureSheet>
     */
    public function signatures(User $user): Collection
    {
        return SignatureSheet::query()
            ->join('signature_sheet_user', 'signature_sheet_user.signature_sheet_id', '=', 'signature_sheets.id')
            ->where('signature_sheet_user.user_id', $user->id)
            ->select('signature_sheets.*', 'signature_sheet_user.created_at as signed_at')
            ->with(['meetingRequest.committee', 'secretary.committee'])
            ->orderByDesc('signed_at')->orderByDesc('signature_sheets.id')->get();
    }

    /**
     * The meeting requests of the committees the person belongs to (by a default list of their secretary, or because
     * they run it), each with how the person shows in it: signed its sheet, appears in its minutes, still to come or
     * no record at all.
     *
     * @return Collection<int, array{request: MeetingRequest, state: string, tone: string, label: string, minutes: ?int}>
     */
    public function expectedRequests(User $user): Collection
    {
        $committees = DefaultList::whereHas('users', fn (Builder $query) => $query->whereKey($user->id))
            ->with('secretary')->get()->pluck('secretary.committee_id')
            ->concat($this->committees($user)->pluck('committee.id'))
            ->filter()->unique()->values();

        if ($committees->isEmpty()) {
            return collect();
        }

        $signed = $this->signatures($user)->pluck('meeting_request_id')->filter()->all();
        $attended = $user->meetings()->pluck('meetings.id')->all();

        $requests = MeetingRequest::whereIn('committee_id', $committees)->with('committee')
            ->orderByDesc('datetime')->orderByDesc('id')->get();
        $minutes = MeetingMinutes::whereIn('meeting_id', $requests->pluck('meeting_id')->filter())->pluck('id', 'meeting_id');

        return $requests
            ->map(function (MeetingRequest $request) use ($signed, $attended, $minutes): array {
                [$state, $tone, $label] = match (true) {
                    in_array($request->id, $signed) => ['signed', 'accepted', 'Firmó'],
                    $request->meeting_id !== null && in_array($request->meeting_id, $attended) => ['minutes', 'accepted', 'En el acta'],
                    $request->datetime !== null && Carbon::parse($request->datetime)->isFuture() => ['upcoming', 'notice', 'Próxima'],
                    default => ['missing', 'rejected', 'Sin constancia'],
                };

                return ['request' => $request, 'state' => $state, 'tone' => $tone, 'label' => $label, 'minutes' => $minutes[$request->meeting_id] ?? null];
            });
    }

    /** @return Collection<int, Attendee> */
    public function attendances(User $user): Collection
    {
        return Attendee::where('user_id', $user->id)->with('event')->get()
            ->sortByDesc(fn (Attendee $attendee): string => (string) ($attendee->event?->start_datetime ?? $attendee->created_at))
            ->values();
    }

    /** @return Collection<int, Bonus> */
    public function bonuses(User $user): Collection
    {
        return $user->bonus()->with('committee')->orderByDesc('bonuses.created_at')->orderByDesc('bonuses.id')->get();
    }

    /**
     * Threads in which the person wrote, with how many messages they wrote in each (written_count).
     *
     * @return Collection<int, MessageThread>
     */
    public function threads(User $user): Collection
    {
        return MessageThread::query()->participatedBy($user)
            ->withCount(['messages as written_count' => fn (Builder $query) => $query->where('user_id', $user->id)])
            ->orderByDesc('last_message_at')->orderByDesc('id')->get();
    }

    /** The annotations of one view of the activity, a page at a time. */
    public function activity(User $user, string $scope): LengthAwarePaginator
    {
        return $this->activityQuery($user, $scope)->with('user')->latest('id')
            ->paginate(self::PAGE_SIZE)->withQueryString()->fragment('secciones');
    }

    /** Every view keeps out the notices sent to the person; every OR stays inside its own where. */
    public function activityQuery(User $user, string $scope): Builder
    {
        $access = ActivityAction::inGroup('access');
        $query = ActivityLog::query()->forUser($user)->where('action', '!=', ActivityAction::NotificationSent->value);

        return match (self::scope($scope)) {
            'hecho' => $query->where('user_id', $user->id)->whereNotIn('action', $access),
            'afecta' => $query->where(fn (Builder $where) => $where->whereNull('user_id')->orWhere('user_id', '!=', $user->id))
                ->whereNotIn('action', $access),
            'accesos' => $query->access(),
            default => $query,
        };
    }

    /**
     * The whole record as the rows of a CSV, the headers first.
     *
     * @return list<list<string|int|float|null>>
     */
    public function rows(User $user): array
    {
        $hours = fn (float|int|string|null $value): string => number_format((float) $value, 2, ',', '');
        $date = fn ($value): ?string => $value === null || $value === '' ? null : Carbon::parse($value)->toIso8601String();
        $account = $this->account($user);
        $committee = fn ($committee): ?string => $committee?->name;

        $rows = [self::CSV_HEADERS];
        $rows[] = [
            'Persona', $date($account['lastLogin']), trim($user->name.' '.$user->surname),
            $account['committees']->map(fn (array $item): string => ($item['role'] === 'COORDINATOR' ? 'Coordinación de ' : 'Secretaría de ').$item['committee']->name)->implode(', '),
            $user->roles->map(fn ($role): string => Roles::label($role->rol))->implode(', '),
            $hours(array_sum($this->origins($user))),
            implode(' · ', array_filter([$account['username'], $account['email'], $account['blocked'] ? 'Bloqueada' : null])),
        ];
        foreach ($this->origins($user) as $label => $value) {
            $rows[] = ['Horas', null, $label, null, null, $hours($value), null];
        }

        foreach ($this->evidences($user) as $evidence) {
            $rows[] = ['Evidencias', $date($evidence->updated_at), $evidence->title, $committee($evidence->committee), $evidence->status_label,
                $hours($evidence->hours), 'Versión '.$evidence->version];
        }
        foreach ($this->meetings($user) as $meeting) {
            $rows[] = ['Reuniones', $date($meeting->datetime), $meeting->title, $committee($meeting->committee), $meeting->meetingMinutes ? 'Con acta' : 'Sin acta',
                $hours($meeting->hours), $meeting->place];
        }
        foreach ($this->signatures($user) as $sheet) {
            $rows[] = ['Firmas', $date($sheet->signed_at), $sheet->title, $committee($sheet->meetingRequest?->committee ?? $sheet->secretary?->committee),
                'Firmada', null, $sheet->meetingRequest?->title];
        }
        foreach ($this->expectedRequests($user) as $item) {
            $rows[] = ['Convocatorias', $date($item['request']->datetime), $item['request']->title, $committee($item['request']->committee), $item['label'],
                null, $item['request']->place];
        }
        foreach ($this->attendances($user) as $attendee) {
            $rows[] = ['Asistencias', $date($attendee->event?->start_datetime), $attendee->event?->name, null, self::ATTENDANCE[$attendee->status] ?? $attendee->status,
                $hours($attendee->computedHours()), null];
        }
        foreach ($this->bonuses($user) as $bonus) {
            $rows[] = ['Bonos', $date($bonus->created_at), $bonus->reason, $committee($bonus->committee), null, $hours($bonus->hours), null];
        }
        $this->activityQuery($user, 'todo')->with('user')->latest('id')->each(function (ActivityLog $entry) use (&$rows, $date): void {
            $rows[] = ['Actividad', $date($entry->created_at), $this->presenter->label($entry), $entry->committee_name, null, null,
                implode(' · ', array_filter([
                    $entry->subject_title,
                    $this->presenter->summary($entry),
                    $entry->user_id === null ? 'Sistema' : 'por '.$this->presenter->actorName($entry),
                    $this->presenter->roleLine($entry),
                ]))];
        });
        foreach ($this->threads($user) as $thread) {
            $rows[] = ['Mensajes', $date($thread->last_message_at), $thread->subject, $committee($thread->committee), $thread->isResolved() ? 'Resuelto' : 'Abierto',
                null, $thread->written_count.' '.($thread->written_count === 1 ? 'mensaje' : 'mensajes').' · Para '.$thread->audienceLabel()];
        }

        return $rows;
    }

    /** The rows as a CSV for a spreadsheet: UTF-8 with BOM, «;» as separator and no cell read as a formula. */
    public static function csv(array $rows): string
    {
        $stream = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($stream, array_map(fn ($value): string => ActivityExport::cell($value === null ? null : (string) $value), $row), ';', '"', '');
        }
        rewind($stream);
        $csv = (string) stream_get_contents($stream);
        fclose($stream);

        return "\u{FEFF}".$csv;
    }

    /**
     * Computed hours of the person by origin, as the summary of the profile counts them (App\View\Dashboard::hours).
     *
     * @return array<string, float>
     */
    private function origins(User $user): array
    {
        return [
            'Evidencias' => (float) $user->evidences_computed_hours(),
            'Reuniones' => (float) $user->meetings_hours(),
            'Asistencia' => (float) $user->events_hours(),
            'Bonos' => (float) $user->bonus_hours(),
        ];
    }

    /** Version number of an evidence along its chain of points_to, safe against a broken or circular chain. */
    private function version(int $id, Collection $chain): int
    {
        $version = 1;
        $seen = [$id => true];
        $current = $chain[$id] ?? null;

        while ($current !== null && $chain->has($current) && ! isset($seen[$current])) {
            $seen[$current] = true;
            $version++;
            $current = $chain[$current];
        }

        return $version;
    }
}
