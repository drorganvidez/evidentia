<?php

namespace App\Services\Activity;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use Closure;
use PHPUnit\Framework\Assert;
use RuntimeException;

/**
 * Recorder of the tests: keeps the annotations in memory, writes nothing and fires no event.
 */
class ActivityFake extends ActivityRecorder
{
    /** @var list<array{log: ActivityLog, targets: list<int>}> */
    private array $recorded = [];

    /** @var list<string> */
    private array $failing = [];

    /** Throws a RuntimeException when this verb is annotated, to check that the writes around it roll back. */
    public function failOn(ActivityAction $action): static
    {
        $this->failing[] = $action->value;

        return $this;
    }

    /** @param  (Closure(ActivityLog, list<int>): bool)|null  $callback */
    public function assertRecorded(ActivityAction $action, ?Closure $callback = null): void
    {
        $matches = array_filter(
            $this->recorded,
            fn (array $entry): bool => $entry['log']->action === $action->value
                && ($callback === null || $callback($entry['log'], $entry['targets'])),
        );

        Assert::assertNotEmpty($matches, "The activity [{$action->value}] was not recorded.");
    }

    public function assertNotRecorded(ActivityAction $action): void
    {
        Assert::assertEmpty(
            array_filter($this->recorded, fn (array $entry): bool => $entry['log']->action === $action->value),
            "The activity [{$action->value}] was recorded.",
        );
    }

    public function assertNothingRecorded(): void
    {
        Assert::assertSame([], array_map(fn (array $entry): string => $entry['log']->action, $this->recorded), 'Some activity was recorded.');
    }

    /** @return list<ActivityLog> */
    public function recorded(): array
    {
        return array_map(fn (array $entry): ActivityLog => $entry['log'], $this->recorded);
    }

    /** @param  list<int>  $targetIds */
    protected function persist(array $attributes, array $targetIds): ActivityLog
    {
        if (in_array($attributes['action'], $this->failing, true)) {
            throw new RuntimeException("Annotating [{$attributes['action']}] failed on purpose.");
        }

        $log = new ActivityLog($attributes);
        $this->recorded[] = ['log' => $log, 'targets' => $targetIds];

        return $log;
    }
}
