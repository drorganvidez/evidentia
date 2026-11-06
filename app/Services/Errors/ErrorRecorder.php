<?php

namespace App\Services\Errors;

use App\Models\ErrorOccurrence;
use App\Models\ErrorReport;
use App\Models\User;
use App\Notifications\ErrorReported;
use App\Services\Activity\ActivityRecorder;
use App\Services\Notifications\Notifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Keeps every server error: in the errors log with its context, grouped in error_reports with its latest
 * occurrences, and announced to the lecturers the first time it happens (or when a resolved one comes back),
 * at most once an hour per error. It never throws: a failure here must not hide the original error.
 */
class ErrorRecorder
{
    private const KEPT_OCCURRENCES = 20;

    /** Shared by every instance: an error inside the recording is never recorded again. */
    private static bool $recording = false;

    public function record(Throwable $exception): void
    {
        if (self::$recording) {
            return;
        }
        self::$recording = true;

        try {
            $context = $this->context();
            Log::channel('errors')->error($exception->getMessage(), $context + [
                'exception' => $exception::class,
                'file' => $exception->getFile().':'.$exception->getLine(),
            ]);

            [$report, $announce] = $this->store($exception, $context);

            if ($announce) {
                $this->announce($report);
            }
        } catch (Throwable) {
            // The default handler still logs the original error
        } finally {
            self::$recording = false;
        }
    }

    /** Who, where and the reference of the request, also added to every log entry of an exception. */
    public function context(): array
    {
        $request = request();
        $route = $request->route();
        $user = Auth::user();

        return array_filter([
            'reference' => $this->reference(),
            'method' => $route !== null ? $request->method() : 'CLI',
            'url' => $route !== null ? $request->fullUrl() : 'artisan '.implode(' ', array_slice($_SERVER['argv'] ?? [], 1)),
            'route' => $route?->getName(),
            'user_id' => $user?->getAuthIdentifier(),
            'user' => $user !== null ? trim($user->name.' '.$user->surname) : null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /** The request id the activity log uses; the error page shows its first eight characters. */
    public function reference(): string
    {
        return app(ActivityRecorder::class)->requestId();
    }

    /** @return array{0: ErrorReport, 1: bool} the report and whether the lecturers hear about it */
    private function store(Throwable $exception, array $context): array
    {
        $fingerprint = sha1($exception::class.'|'.$exception->getFile().'|'.$exception->getLine());

        return DB::transaction(function () use ($exception, $context, $fingerprint): array {
            $now = now();
            $report = ErrorReport::where('fingerprint', $fingerprint)->lockForUpdate()->first()
                ?? new ErrorReport(['fingerprint' => $fingerprint, 'first_seen_at' => $now]);
            $comesBack = $report->exists && $report->resolved_at !== null;
            $isNew = ! $report->exists;

            $report->fill([
                'exception' => $exception::class,
                'message' => Str::limit($exception->getMessage(), 2000),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => implode("\n", array_slice(explode("\n", $exception->getTraceAsString()), 0, 25)),
                'occurrences' => $report->occurrences + 1,
                'last_seen_at' => $now,
                'resolved_at' => null,
            ]);
            // Announced when it is new or comes back, never more than once an hour
            $announce = ($isNew || $comesBack)
                && ($report->notified_at === null || $report->notified_at->lt($now->copy()->subHour()));
            if ($announce) {
                $report->notified_at = $now;
            }
            $report->save();

            ErrorOccurrence::create([
                'error_report_id' => $report->id,
                'reference' => $context['reference'],
                'method' => $context['method'] ?? null,
                'url' => isset($context['url']) ? Str::limit($context['url'], 2000) : null,
                'route' => $context['route'] ?? null,
                'user_id' => $context['user_id'] ?? null,
                'user_name' => $context['user'] ?? null,
                'occurred_at' => $now,
            ]);
            $stale = $report->latestOccurrences()->skip(self::KEPT_OCCURRENCES)->take(PHP_INT_MAX)->pluck('id');
            if ($stale->isNotEmpty()) {
                ErrorOccurrence::whereIn('id', $stale)->delete();
            }

            return [$report, $announce];
        });
    }

    private function announce(ErrorReport $report): void
    {
        $send = function () use ($report): void {
            $lecturers = User::where('block', false)
                ->whereHas('roles', fn ($query) => $query->where('rol', 'LECTURE'))
                ->get();
            // One by one and catching: a mail server down must not raise another error from here
            app(Notifier::class)->send($lecturers, new ErrorReported($report));
        };

        // After the response, so the person who hit the error does not wait for the mail; a command (and a test,
        // whose application keeps its terminating callbacks between requests) sends it right away
        if (app()->runningInConsole()) {
            $send();
        } else {
            app()->terminating($send);
        }
    }
}
