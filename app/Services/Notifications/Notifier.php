<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Notifications\BaseNotification;
use Throwable;

/**
 * Sends a notice person by person: a failed mail never stops the rest nor breaks the request. It annotates nothing;
 * notification.failed comes only from the listener of NotificationFailed.
 */
final class Notifier
{
    /**
     * @param  iterable<User|null>  $users
     * @return int notices handed over
     */
    public function send(iterable $users, BaseNotification $notification, ?int $exceptUserId = null): int
    {
        $sent = 0;

        foreach (collect($users)->filter()->unique(fn (User $user) => $user->getKey()) as $user) {
            if ($exceptUserId !== null && (int) $user->getKey() === $exceptUserId) {
                continue;
            }

            try {
                $user->notify(clone $notification);
                $sent++;
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $sent;
    }
}
