<?php

namespace App\Listeners;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\User;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Str;
use Throwable;

/**
 * One notification.sent per notice (on the first channel it took) and one notification.failed per failed channel.
 * Discovered, never registered by hand.
 */
final class RecordNotificationActivity
{
    public function handleSent(NotificationSent $event): void
    {
        $notification = $event->notification;
        if (! $notification instanceof BaseNotification || ! $event->notifiable instanceof User) {
            return;
        }
        if ($event->channel !== ($notification->channels[0] ?? $event->channel)) {
            return;
        }

        Activity::system(
            ActivityAction::NotificationSent,
            subject: $notification->subject(),
            target: $event->notifiable,
            subjectTitle: $notification->activity?->subject_title,
            committeeId: false,
            details: array_filter([
                'type' => $notification::key(),
                'level' => $notification::level()->value,
                'title' => $notification->title($event->notifiable),
                'channels' => $notification->channels ?: [$event->channel],
                'notification_id' => $event->channel === 'database' ? $notification->id : null,
                'activity_id' => $notification->activity?->id,
            ], fn ($value): bool => $value !== null),
        );
    }

    public function handleFailed(NotificationFailed $event): void
    {
        $notification = $event->notification;
        if (! $notification instanceof BaseNotification || ! $event->notifiable instanceof User) {
            return;
        }

        $exception = $event->data['exception'] ?? null;

        Activity::system(
            ActivityAction::NotificationFailed,
            subject: $notification->subject(),
            target: $event->notifiable,
            subjectTitle: $notification->activity?->subject_title,
            committeeId: false,
            details: array_filter([
                'type' => $notification::key(),
                'channel' => $event->channel,
                'error' => $exception instanceof Throwable ? Str::limit($exception->getMessage(), 200) : null,
                'activity_id' => $notification->activity?->id,
            ], fn ($value): bool => $value !== null),
        );
    }
}
