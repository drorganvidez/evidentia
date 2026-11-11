<?php

namespace Tests\Unit;

use App\Models\WorkSession;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Equivalence partitions and boundary values of the sum of work sessions into the hours and minutes of the form:
 * 0 s, under a minute, the 59 → 60 minutes frontier and three or more sessions.
 */
class WorkSessionDurationTest extends TestCase
{
    private function stopped(int $seconds, string $title = 'Sesión'): WorkSession
    {
        return new WorkSession(['title' => $title, 'status' => WorkSession::STATUS_STOPPED, 'accumulated_seconds' => $seconds]);
    }

    /** @return array<string, array{int, int, int}> seconds, hours, minutes */
    public static function boundaries(): array
    {
        return [
            'zero seconds' => [0, 0, 0],
            'one second counts as a minute' => [1, 0, 1],
            'half a minute counts as a minute' => [30, 0, 1],
            'last second under a minute' => [59, 0, 1],
            'exactly one minute' => [60, 0, 1],
            'one minute and a half is one whole minute' => [119, 0, 1],
            'two minutes' => [120, 0, 2],
            'first second of minute 59' => [3540, 0, 59],
            'last second of minute 59' => [3599, 0, 59],
            'exactly sixty minutes is one hour sharp' => [3600, 1, 0],
            'one hour and one minute' => [3660, 1, 1],
            'one hour and fifty nine minutes' => [7199, 1, 59],
            'two hours sharp' => [7200, 2, 0],
            'a negative value is clamped to zero' => [-45, 0, 0],
        ];
    }

    #[DataProvider('boundaries')]
    public function test_seconds_become_the_hours_and_minutes_of_the_form(int $seconds, int $hours, int $minutes): void
    {
        $duration = WorkSession::secondsToHoursAndMinutes($seconds);

        $this->assertSame($hours, $duration['hours'], 'hours of '.$seconds.' s');
        $this->assertSame($minutes, $duration['minutes'], 'minutes of '.$seconds.' s');
        $this->assertSame($hours * 60 + $minutes, $duration['total_minutes']);
    }

    public function test_minutes_never_reach_sixty_so_they_stay_valid_for_the_form(): void
    {
        foreach (range(0, 4 * 3600, 37) as $seconds) {
            $this->assertLessThan(60, WorkSession::secondsToHoursAndMinutes($seconds)['minutes'], $seconds.' s');
        }
    }

    public function test_a_single_session_gives_its_title_and_duration(): void
    {
        $summary = WorkSession::summarizeSessions([$this->stopped(5400, 'Montaje del stand')]);

        $this->assertSame(['title' => 'Montaje del stand', 'hours' => 1, 'minutes' => 30, 'total_seconds' => 5400, 'total_minutes' => 90], $summary);
    }

    public function test_no_sessions_sum_zero(): void
    {
        $summary = WorkSession::summarizeSessions([]);

        $this->assertSame(['title' => '', 'hours' => 0, 'minutes' => 0], [
            'title' => $summary['title'], 'hours' => $summary['hours'], 'minutes' => $summary['minutes'],
        ]);
    }

    public function test_three_sessions_add_their_exact_seconds_before_rounding(): void
    {
        // 20 s + 20 s + 20 s are 60 s: one minute, where adding the rounded minutes would give three
        $summary = WorkSession::summarizeSessions([$this->stopped(20, 'A'), $this->stopped(20, 'B'), $this->stopped(20, 'C')]);

        $this->assertSame(60, $summary['total_seconds']);
        $this->assertSame([0, 1], [$summary['hours'], $summary['minutes']]);
    }

    public function test_sessions_that_cross_the_hour_together_carry_into_hours(): void
    {
        // 30 min + 20 min + 9 min 59 s = 59 min 59 s; one more second makes 1 h 00 min
        $below = WorkSession::summarizeSessions([$this->stopped(1800), $this->stopped(1200), $this->stopped(599)]);
        $edge = WorkSession::summarizeSessions([$this->stopped(1800), $this->stopped(1200), $this->stopped(600)]);

        $this->assertSame([0, 59], [$below['hours'], $below['minutes']]);
        $this->assertSame([1, 0], [$edge['hours'], $edge['minutes']]);
    }

    public function test_four_sessions_sum_to_hours_and_minutes(): void
    {
        $summary = WorkSession::summarizeSessions([$this->stopped(3600), $this->stopped(1800), $this->stopped(900), $this->stopped(300)]);

        $this->assertSame(6600, $summary['total_seconds']);
        $this->assertSame([1, 50], [$summary['hours'], $summary['minutes']]);
    }

    public function test_the_titles_are_joined_without_repeating_or_blanks(): void
    {
        $summary = WorkSession::summarizeSessions([
            $this->stopped(60, 'Montaje'), $this->stopped(60, '  Montaje  '), $this->stopped(60, 'Cartelería'), $this->stopped(60, '   '),
        ]);

        $this->assertSame('Montaje · Cartelería', $summary['title']);
    }

    public function test_a_long_joined_title_fits_the_title_column(): void
    {
        $sessions = array_map(fn (int $i): WorkSession => $this->stopped(60, str_repeat('x', 100).$i), range(1, 5));

        $this->assertLessThanOrEqual(255, mb_strlen(WorkSession::summarizeSessions($sessions)['title']));
    }

    public function test_a_running_session_counts_the_segment_in_progress(): void
    {
        $now = CarbonImmutable::parse('2026-11-11 10:00:00');
        $running = new WorkSession([
            'title' => 'En curso', 'status' => WorkSession::STATUS_RUNNING, 'accumulated_seconds' => 120, 'started_at' => $now->subSeconds(180),
        ]);
        $paused = new WorkSession(['title' => 'En pausa', 'status' => WorkSession::STATUS_PAUSED, 'accumulated_seconds' => 90]);

        $this->assertSame(300, $running->elapsedSeconds($now));
        $this->assertSame(90, $paused->elapsedSeconds($now->addHour()), 'A paused session does not keep counting');
        $this->assertSame(390, WorkSession::summarizeSessions([$running, $paused], $now)['total_seconds']);
    }

    public function test_the_clock_is_printed_as_hours_minutes_and_seconds(): void
    {
        $this->assertSame('00:00:00', WorkSession::formatClock(0));
        $this->assertSame('00:00:59', WorkSession::formatClock(59));
        $this->assertSame('00:59:59', WorkSession::formatClock(3599));
        $this->assertSame('01:00:00', WorkSession::formatClock(3600));
        $this->assertSame('100:00:01', WorkSession::formatClock(360001));
    }
}
