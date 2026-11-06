<?php

namespace App\Console\Commands;

use App\Facades\Activity;
use App\Models\User;
use App\Notifications\EvidenceDigest;
use App\Services\Notifications\Notifier;
use App\Services\Notifications\ReminderRegistry;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;

/**
 * One mail a day to the coordinators and secretaries who chose the digest, with the evidences that reached their
 * inbox since their last digest (or in the last day). Exact window and no repeats, even if the scheduler restarts.
 */
final class SendDailyDigests extends Command
{
    protected $signature = 'notifications:digest';

    protected $description = 'Envía el resumen diario de evidencias recibidas a coordinadores y secretarios';

    public function handle(ReminderRegistry $registry, Notifier $notifier): int
    {
        Activity::startRequest();
        $sent = 0;

        $people = User::query()
            ->whereHas('notificationPreference', fn (Builder $preference) => $preference->where('digest', true))
            ->whereHas('roles', fn (Builder $roles) => $roles->whereIn('rol', ['COORDINATOR', 'SECRETARY']))
            ->with('roles')
            ->get();

        foreach ($people as $user) {
            $since = $registry->lastSentAt($user, 'digest:') ?? now()->subDay();
            $items = $user->notifications()
                ->where('type', 'evidence.submitted')
                ->where('created_at', '>', $since)
                ->reorder()->oldest()
                ->get()
                ->map(fn (DatabaseNotification $notification): string => (string) ($notification->data['digest'] ?? $notification->data['text'] ?? $notification->data['title']))
                ->values()
                ->all();

            if ($items === [] || ! $registry->claim($user, 'digest:'.today()->toDateString())) {
                continue;
            }

            $sent += $notifier->send([$user], new EvidenceDigest($items));
        }

        $this->info('evidence.digest · '.$sent);

        return self::SUCCESS;
    }
}
