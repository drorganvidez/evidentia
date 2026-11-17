<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PaginatesLists;
use App\Models\Agreement;
use App\Models\Committee;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Support\ListPage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Search over the minutes of every committee: the agreements by words or by their identifier, the meetings by words,
 * and every agreement of one meeting (?acta=), filtered by committee and dates. Any signed-in person may search; the
 * minutes are open to all of them.
 */
class AgreementSearchController extends Controller
{
    use PaginatesLists;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'reuniones' ? 'reuniones' : 'acuerdos';
        $term = ListPage::term($request);
        $committees = Committee::orderBy('name')->get(['id', 'name']);
        $filters = [
            'comite' => in_array((int) $request->query('comite'), $committees->pluck('id')->all(), true) ? (int) $request->query('comite') : null,
            'desde' => $this->date($request, 'desde'),
            'hasta' => $this->date($request, 'hasta'),
        ];
        $minutes = $tab === 'acuerdos' && ctype_digit((string) $request->query('acta'))
            ? MeetingMinutes::with('meeting')->find((int) $request->query('acta'))
            : null;

        return view('meeting.search', [
            'tab' => $tab,
            'term' => $term,
            'filters' => $filters,
            'committees' => $committees->map(fn (Committee $committee): array => ['value' => (string) $committee->id, 'label' => $committee->name])->all(),
            'minutes' => $minutes,
            'agreements' => $tab === 'acuerdos' ? $this->agreements($request, $term, $minutes, $filters) : null,
            'meetings' => $tab === 'reuniones' ? $this->meetings($request, $filters) : null,
        ]);
    }

    private function agreements(Request $request, ?string $term, ?MeetingMinutes $minutes, array $filters)
    {
        $query = $this->filterMeetings(
            Agreement::query()
                ->select('agreements.*')
                ->join('points', 'points.id', '=', 'agreements.point_id')
                ->join('meeting_minutes', 'meeting_minutes.id', '=', 'points.meeting_minutes_id')
                ->join('meetings', 'meetings.id', '=', 'meeting_minutes.meeting_id')
                ->with('point.meetingMinutes.meeting.committee'),
            $filters,
        );

        if ($minutes !== null) {
            $query->where('points.meeting_minutes_id', $minutes->id);
        }

        $this->searchList($query, $request, ['identificator', 'description', 'point.title', 'point.meetingMinutes.meeting.title']);

        // the agreement whose identifier is exactly the search goes first
        if ($term !== null) {
            $query->orderByRaw('lower(agreements.identificator) = ? desc', [mb_strtolower($term)]);
        }

        return $this->paginateList($query->orderByDesc('meetings.datetime')->orderBy('points.id')->orderBy('agreements.id'), $request);
    }

    private function meetings(Request $request, array $filters)
    {
        $query = $this->filterMeetings(
            Meeting::query()
                ->whereHas('meetingMinutes')
                ->with(['committee', 'meetingMinutes'])
                ->addSelect(['agreements_count' => Agreement::query()
                    ->selectRaw('count(*)')
                    ->join('points', 'points.id', '=', 'agreements.point_id')
                    ->join('meeting_minutes', 'meeting_minutes.id', '=', 'points.meeting_minutes_id')
                    ->whereColumn('meeting_minutes.meeting_id', 'meetings.id'),
                ]),
            $filters,
        );

        $this->searchList($query, $request, [
            'title', 'place', 'committee.name',
            'meetingMinutes.points.title', 'meetingMinutes.points.description', 'meetingMinutes.points.agreements.description',
        ]);

        return $this->paginateList($query->orderByDesc('datetime'), $request);
    }

    private function filterMeetings(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['comite'], fn (Builder $query, int $id) => $query->where('meetings.committee_id', $id))
            ->when($filters['desde'], fn (Builder $query, Carbon $date) => $query->where('meetings.datetime', '>=', $date->copy()->startOfDay()))
            ->when($filters['hasta'], fn (Builder $query, Carbon $date) => $query->where('meetings.datetime', '<=', $date->copy()->endOfDay()));
    }

    /** A date of the query string in Y-m-d, or null when missing or malformed. */
    private function date(Request $request, string $key): ?Carbon
    {
        $value = $request->query($key);
        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }
        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);

            return $date && $date->format('Y-m-d') === $value ? $date : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
