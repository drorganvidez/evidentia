<?php

namespace App\Services\Notifications;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Reminders and digests already sent, so that a restart of the scheduler never sends one twice.
 */
final class ReminderRegistry
{
    /** Takes the key for the person; true only the first time. */
    public function claim(User $user, string $key): bool
    {
        return DB::table('notification_reminders')->insertOrIgnore([
            'user_id' => $user->getKey(),
            'key' => $key,
            'sent_at' => now(),
        ]) === 1;
    }

    /** When the person last got a reminder whose key starts with the prefix (digest: for the digests). */
    public function lastSentAt(User $user, string $prefix): ?CarbonImmutable
    {
        $last = DB::table('notification_reminders')
            ->where('user_id', $user->getKey())
            ->get(['key', 'sent_at'])
            ->filter(fn (object $reminder): bool => str_starts_with($reminder->key, $prefix))
            ->max('sent_at');

        return $last === null ? null : CarbonImmutable::parse($last);
    }
}
