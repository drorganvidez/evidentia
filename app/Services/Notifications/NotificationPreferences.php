<?php

namespace App\Services\Notifications;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Facades\Activity;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Catalog;
use App\Notifications\Digestible;
use Illuminate\Support\Facades\DB;

/**
 * Which channels a notice takes for a person. Fixed notices always go by mail, important ones unless turned off,
 * informative ones only if turned on; a blocked person only gets the inbox. Only the deviations are stored.
 */
final class NotificationPreferences
{
    /** @return list<string> */
    public function channelsFor(User $user, BaseNotification $notification): array
    {
        if ($user->block) {
            return ['database'];
        }

        return $this->mailEnabled($user, $notification) ? ['database', 'mail'] : ['database'];
    }

    public function mailEnabled(User $user, BaseNotification|string $notification): bool
    {
        $class = match (true) {
            $notification instanceof BaseNotification => $notification::class,
            is_subclass_of($notification, BaseNotification::class) => $notification,
            default => Catalog::byKey($notification),
        };
        if ($class === null) {
            return false;
        }

        if ($class::level() === NotificationLevel::Fixed) {
            return true;
        }

        if (is_subclass_of($class, Digestible::class) && $this->digest($user) && $this->canDigest($user)) {
            return false;
        }

        $saved = $this->preference($user)?->mail[$class::key()] ?? null;

        return is_bool($saved) ? $saved : $class::level() === NotificationLevel::Important;
    }

    public function digest(User $user): bool
    {
        return (bool) $this->preference($user)?->digest;
    }

    /**
     * Saves the mail choices of the preferences screen (the keys with mail on) and the digest, and annotates the
     * change.
     *
     * @param  list<string>  $mailKeys
     */
    public function update(User $user, array $mailKeys, bool $digest): NotificationPreference
    {
        $current = $this->preference($user);
        $before = ['mail' => $current?->mail ?? [], 'digest' => (bool) $current?->digest];
        $mail = $before['mail'];

        foreach (Catalog::forPreferences($user) as $level => $classes) {
            if ($level === NotificationLevel::Fixed->value) {
                continue;
            }
            foreach ($classes as $class) {
                $wanted = in_array($class::key(), $mailKeys, true);
                $default = $class::level() === NotificationLevel::Important;
                if ($wanted === $default) {
                    unset($mail[$class::key()]);
                } else {
                    $mail[$class::key()] = $wanted;
                }
            }
        }
        ksort($mail);
        $after = ['mail' => $mail, 'digest' => $digest && $this->canDigest($user)];

        $preference = DB::transaction(function () use ($user, $before, $after): NotificationPreference {
            $preference = NotificationPreference::updateOrCreate(
                ['user_id' => $user->getKey()],
                ['mail' => $after['mail'] === [] ? null : $after['mail'], 'digest' => $after['digest']],
            );

            Activity::record(
                ActivityAction::NotificationPreferencesChanged,
                $user,
                details: ['before' => $before, 'after' => $after],
                deadline: false,
                committeeId: false,
            );

            return $preference;
        });

        $user->setRelation('notificationPreference', $preference);

        return $preference;
    }

    /** Only coordinators and secretaries receive the daily digest. */
    private function canDigest(User $user): bool
    {
        return $user->hasRole('COORDINATOR') || $user->hasRole('SECRETARY');
    }

    private function preference(User $user): ?NotificationPreference
    {
        return $user->notificationPreference;
    }
}
