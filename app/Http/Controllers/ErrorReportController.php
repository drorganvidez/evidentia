<?php

namespace App\Http\Controllers;

use App\Models\ErrorOccurrence;
use App\Models\ErrorReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The server errors for the lecturers and the president: grouped, with their occurrences, resolved by hand and
 * reopened if they return. Each one reaches them under the routes of their own role (lecture.* or president.*).
 */
class ErrorReportController extends Controller
{
    public function index(Request $request)
    {
        $state = in_array($request->query('estado'), ['abiertos', 'resueltos', 'todos'], true)
            ? $request->query('estado')
            : 'abiertos';
        $code = strtoupper(trim((string) $request->query('codigo', '')));

        $reports = ErrorReport::query()
            ->when($state === 'abiertos', fn ($query) => $query->open())
            ->when($state === 'resueltos', fn ($query) => $query->resolved())
            ->when($code !== '', fn ($query) => $query->whereIn(
                'id',
                ErrorOccurrence::where('reference', 'like', strtolower($code).'%')->select('error_report_id'),
            ))
            ->orderByDesc('last_seen_at')
            ->paginate(25)
            ->withQueryString();

        return view('system.errors.index', [
            'reports' => $reports,
            'state' => $state,
            'code' => $code,
            'prefix' => $this->prefix(),
            'counts' => [
                'abiertos' => ErrorReport::open()->count(),
                'resueltos' => ErrorReport::resolved()->count(),
                'todos' => ErrorReport::count(),
            ],
        ]);
    }

    public function show(int $id)
    {
        $report = ErrorReport::with('latestOccurrences')->findOrFail($id);

        return view('system.errors.show', ['report' => $report, 'prefix' => $this->prefix()]);
    }

    public function resolve(int $id)
    {
        ErrorReport::findOrFail($id)->update(['resolved_at' => now()]);

        return redirect()->route($this->prefix().'.errors')->with('success', 'Error marcado como resuelto.');
    }

    public function reopen(int $id)
    {
        ErrorReport::findOrFail($id)->update(['resolved_at' => null]);

        return redirect()->route($this->prefix().'.errors.show', $id)->with('success', 'Error reabierto.');
    }

    private function prefix(): string
    {
        return Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president';
    }
}
