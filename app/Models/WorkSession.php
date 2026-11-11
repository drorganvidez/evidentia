<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Timed work session owned by a single user. Tracks elapsed seconds across start/pause/resume/stop cycles and
 * converts one or several sessions into the hours and minutes expected by the evidence creation form.
 */
class WorkSession extends Model
{
    use HasFactory;

    public const STATUS_RUNNING = 'running';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_STOPPED = 'stopped';

    protected $table = 'work_sessions';

    protected $fillable = [
        'user_id',
        'title',
        'status',
        'accumulated_seconds',
        'started_at',
        'stopped_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'accumulated_seconds' => 'integer',
            'started_at' => 'datetime',
            'stopped_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOwnedBy(User|int $user): bool
    {
        $userId = $user instanceof User ? (int) $user->id : (int) $user;

        return (int) $this->user_id === $userId;
    }

    /**
     * Total elapsed seconds of the session up to $now, including the active running segment if any.
     */
    public function elapsedSeconds(?CarbonInterface $now = null): int
    {
        $accumulated = max(0, (int) $this->accumulated_seconds);

        if ($this->status !== self::STATUS_RUNNING || $this->started_at === null) {
            return $accumulated;
        }

        $reference = $now ?? now();
        $runningDelta = max(0, (int) $this->started_at->diffInSeconds($reference, false));

        return $accumulated + $runningDelta;
    }

    public function pause(?CarbonInterface $now = null): self
    {
        if ($this->status === self::STATUS_RUNNING) {
            $this->accumulated_seconds = $this->elapsedSeconds($now);
            $this->status = self::STATUS_PAUSED;
            $this->started_at = null;
            $this->save();
        }

        return $this;
    }

    public function resume(?CarbonInterface $now = null): self
    {
        if ($this->status === self::STATUS_PAUSED) {
            $this->status = self::STATUS_RUNNING;
            $this->started_at = $now ?? now();
            $this->save();
        }

        return $this;
    }

    public function stop(?CarbonInterface $now = null): self
    {
        if ($this->status !== self::STATUS_STOPPED) {
            $reference = $now ?? now();
            $this->accumulated_seconds = $this->elapsedSeconds($reference);
            $this->status = self::STATUS_STOPPED;
            $this->started_at = null;
            $this->stopped_at = $reference;
            $this->save();
        }

        return $this;
    }

    /**
     * Converts a total number of seconds into the hours and minutes fields of the evidence form.
     *
     * - 0 seconds -> 0h 0m
     * - 1..59 seconds (< 1 minute of positive work) -> 0h 1m (minimum computable minute for a valid evidence)
     * - >= 60 seconds -> whole elapsed minutes split into hours (intdiv(minutes, 60)) and minutes (minutes % 60),
     *   so 59 minutes (3540..3599s) -> 0h 59m and 60 minutes (3600s) -> 1h 0m.
     *
     * @return array{hours: int, minutes: int, total_seconds: int, total_minutes: int}
     */
    public static function secondsToHoursAndMinutes(int $totalSeconds): array
    {
        $clampedSeconds = max(0, $totalSeconds);
        $totalMinutes = $clampedSeconds === 0 ? 0 : max(1, intdiv($clampedSeconds, 60));

        return [
            'hours' => intdiv($totalMinutes, 60),
            'minutes' => $totalMinutes % 60,
            'total_seconds' => $clampedSeconds,
            'total_minutes' => $totalMinutes,
        ];
    }

    /**
     * Sums the elapsed seconds of one or multiple work sessions and returns the combined title and duration
     * for pre-filling the evidence form.
     *
     * @param  iterable<int, self>  $sessions
     * @return array{title: string, hours: int, minutes: int, total_seconds: int, total_minutes: int}
     */
    public static function summarizeSessions(iterable $sessions, ?CarbonInterface $now = null): array
    {
        $totalSeconds = 0;
        $titles = [];

        foreach ($sessions as $session) {
            if (! $session instanceof self) {
                continue;
            }

            $totalSeconds += $session->elapsedSeconds($now);
            $cleanTitle = trim((string) $session->title);
            if ($cleanTitle !== '' && ! in_array($cleanTitle, $titles, true)) {
                $titles[] = $cleanTitle;
            }
        }

        $duration = self::secondsToHoursAndMinutes($totalSeconds);

        return [
            'title' => Str::limit(implode(' · ', $titles), 255, ''),
            ...$duration,
        ];
    }

    /**
     * Formats a duration in seconds as HH:MM:SS for display in the topbar stopwatch and session list.
     */
    public static function formatClock(int $seconds): string
    {
        $clamped = max(0, $seconds);
        $hours = intdiv($clamped, 3600);
        $minutes = intdiv($clamped % 3600, 60);
        $secs = $clamped % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}

