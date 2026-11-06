<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

/**
 * The scheduled commands, their time and their protection against overlapping runs.
 */
class ScheduleTest extends TestCase
{
    private function event(string $command): ?Event
    {
        return collect(app(Schedule::class)->events())
            ->first(fn (Event $event): bool => str_contains((string) $event->command, $command));
    }

    public function test_the_activity_log_is_pruned_every_night(): void
    {
        $event = $this->event('activity:prune');

        $this->assertNotNull($event);
        $this->assertSame('0 4 * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
        $this->assertTrue($event->onOneServer);
        $this->assertSame('Europe/Madrid', (string) ($event->timezone ?? config('app.timezone')));
    }
}
