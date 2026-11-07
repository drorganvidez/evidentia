<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PaginatesLists;
use App\Models\Agreement;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Support\ListPage;
use Illuminate\Http\Request;

/**
 * Search over the minutes of every committee: the agreements by words or by their identifier, the meetings by words,
 * and every agreement of one meeting (?acta=). Any signed-in person may search; the minutes are open to all of them.
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
        $minutes = $tab === 'acuerdos' && ctype_digit((string) $request->query('acta'))
            ? MeetingMinutes::with('meeting')->find((int) $request->query('acta'))
            : null;

        return view('meeting.search', [
            'tab' => $tab,
            'term' => $term,
            'minutes' => $minutes,
            'agreements' => $tab === 'acuerdos' ? $this->agreements($request, $term, $minutes) : null,
            'meetings' => $tab === 'reuniones' ? $this->meetings($request) : null,
        ]);
    }

    private function agreements(Request $request, ?string $term, ?MeetingMinutes $minutes)
    {
        $query = Agreement::query()
            ->select('agreements.*')
            ->join('points', 'points.id', '=', 'agreements.point_id')
            ->join('meeting_minutes', 'meeting_minutes.id', '=', 'points.meeting_minutes_id')
            ->join('meetings', 'meetings.id', '=', 'meeting_minutes.meeting_id')
            ->with('point.meetingMinutes.meeting.committee');

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

    private function meetings(Request $request)
    {
        $query = Meeting::query()
            ->whereHas('meetingMinutes')
            ->with(['committee', 'meetingMinutes'])
            ->addSelect(['agreements_count' => Agreement::query()
                ->selectRaw('count(*)')
                ->join('points', 'points.id', '=', 'agreements.point_id')
                ->join('meeting_minutes', 'meeting_minutes.id', '=', 'points.meeting_minutes_id')
                ->whereColumn('meeting_minutes.meeting_id', 'meetings.id'),
            ]);

        $this->searchList($query, $request, [
            'title', 'place', 'committee.name',
            'meetingMinutes.points.title', 'meetingMinutes.points.description', 'meetingMinutes.points.agreements.description',
        ]);

        return $this->paginateList($query->orderByDesc('datetime'), $request);
    }
}
