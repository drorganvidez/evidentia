<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckRoles;
use App\Models\User;
use App\Models\WorkSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Manages the personal work stopwatch sessions of the authenticated user (start, pause, resume, stop, delete, and
 * list recent sessions). Enforces strict ownership so a user can never inspect or mutate another person's sessions.
 */
class WorkSessionController extends Controller
{
    private const RECENT_LIMIT = 15;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':PRESIDENT,COORDINATOR,REGISTER_COORDINATOR,SECRETARY,STUDENT');
    }

    public function index(): JsonResponse
    {
        return response()->json(self::stateFor(Auth::user()));
    }

    public function start(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $now = now();

        $session = DB::transaction(function () use ($user, $validated, $now): WorkSession {
            WorkSession::query()
                ->where('user_id', $user->id)
                ->where('status', WorkSession::STATUS_RUNNING)
                ->get()
                ->each(fn (WorkSession $running) => $running->pause($now));

            return WorkSession::create([
                'user_id' => $user->id,
                'title' => trim($validated['title']),
                'status' => WorkSession::STATUS_RUNNING,
                'accumulated_seconds' => 0,
                'started_at' => $now,
            ]);
        });

        return $this->respond($request, $session, 201);
    }

    public function pause(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $session = $this->findOwnedSession($id);
        $this->syncOptionalTitle($request, $session);
        $session->pause();

        return $this->respond($request, $session);
    }

    public function resume(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $session = $this->findOwnedSession($id);
        $this->syncOptionalTitle($request, $session);
        $now = now();

        DB::transaction(function () use ($session, $now): void {
            WorkSession::query()
                ->where('user_id', $session->user_id)
                ->whereKeyNot($session->id)
                ->where('status', WorkSession::STATUS_RUNNING)
                ->get()
                ->each(fn (WorkSession $running) => $running->pause($now));

            $session->resume($now);
        });

        return $this->respond($request, $session);
    }

    public function stop(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $session = $this->findOwnedSession($id);
        $this->syncOptionalTitle($request, $session);
        $session->stop();

        return $this->respond($request, $session);
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $session = $this->findOwnedSession($id);
        $session->delete();

        if ($request->expectsJson()) {
            return response()->json(self::stateFor(Auth::user()));
        }

        return redirect()->back();
    }

    /**
     * Composes the stopwatch state (active running/paused session + recent stopped sessions) for the given user.
     *
     * @return array{active: ?array<string, mixed>, recent: list<array<string, mixed>>, create_url: string}
     */
    public static function stateFor(User $user): array
    {
        $now = now();

        $active = WorkSession::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [WorkSession::STATUS_RUNNING, WorkSession::STATUS_PAUSED])
            ->orderByRaw("CASE WHEN status = 'running' THEN 0 ELSE 1 END")
            ->latest('updated_at')
            ->latest('id')
            ->first();

        $recent = WorkSession::query()
            ->where('user_id', $user->id)
            ->where('status', WorkSession::STATUS_STOPPED)
            ->latest('stopped_at')
            ->latest('id')
            ->limit(self::RECENT_LIMIT)
            ->get();

        return [
            'active' => $active ? self::formatSession($active, $now) : null,
            'recent' => $recent->map(fn (WorkSession $item): array => self::formatSession($item, $now))->values()->all(),
            'create_url' => route('evidence.create'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function formatSession(WorkSession $session, $now = null): array
    {
        $reference = $now ?? now();
        $elapsed = $session->elapsedSeconds($reference);
        $duration = WorkSession::secondsToHoursAndMinutes($elapsed);

        return [
            'id' => (int) $session->id,
            'title' => (string) $session->title,
            'status' => (string) $session->status,
            'accumulated_seconds' => (int) $session->accumulated_seconds,
            'elapsed_seconds' => $elapsed,
            'formatted_clock' => WorkSession::formatClock($elapsed),
            'hours' => $duration['hours'],
            'minutes' => $duration['minutes'],
            'started_at_ms' => $session->status === WorkSession::STATUS_RUNNING && $session->started_at !== null
                ? $session->started_at->getTimestampMs()
                : null,
            'create_evidence_url' => route('evidence.create', ['sessions' => [$session->id]]),
        ];
    }

    private function findOwnedSession(int $id): WorkSession
    {
        $session = WorkSession::findOrFail($id);
        abort_unless($session->isOwnedBy(Auth::user()), 403);

        return $session;
    }

    private function syncOptionalTitle(Request $request, WorkSession $session): void
    {
        if ($request->filled('title')) {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
            ]);
            $session->title = trim($validated['title']);
        }
    }

    private function respond(Request $request, WorkSession $session, int $status = 200): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'session' => self::formatSession($session->fresh() ?? $session),
                ...self::stateFor(Auth::user()),
            ], $status);
        }

        return redirect()->back()->with('work_session_id', $session->id);
    }
}

