<?php

namespace App\Listeners;

use App\Events\ActivityRecorded;
use App\Notifications\Catalog;
use App\Services\Notifications\Notifier;
use Throwable;

/**
 * Sends the notices of an annotation once it is committed, never to whoever did it. Discovered, never registered by
 * hand.
 */
final class NotifyFromActivity
{
    public function __construct(private Notifier $notifier) {}

    public function handle(ActivityRecorded $event): void
    {
        $activity = $event->activity;

        foreach (Catalog::triggeredBy($activity->action) as $class) {
            try {
                $this->notifier->send(
                    $class::recipients($activity),
                    new $class($activity),
                    exceptUserId: $activity->user_id === null ? null : (int) $activity->user_id,
                );
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
