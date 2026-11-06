<?php

namespace App\Console\Commands;

use App\Facades\Activity;
use App\Models\Evidence;
use App\Notifications\Reminders\ReviewQueueReminder;
use App\Services\Notifications\Notifier;
use App\Services\Notifications\ReminderRegistry;
use App\Services\ReviewQueue;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

/**
 * The weekly summary of the review queues (App\Services\ReviewQueue) for the coordinators and the presidency: on
 * Monday to whoever has pending evidences, on Thursday again to whoever still has some of more than a week. Once per
 * person, queue and week, even if the scheduler restarts; nothing to whoever has nothing pending.
 */
final class SendReviewQueueReminders extends Command
{
    protected $signature = 'notifications:review-queue
        {--kind= : summary (el resumen del lunes) o insist (la insistencia del jueves); por defecto, según el día}
        {--dry-run : Lista los avisos sin enviarlos}';

    protected $description = 'Envía el resumen semanal de evidencias por revisar a coordinadores y presidencia';

    /** Oldest evidences listed in the notice */
    public const ITEMS = 5;

    public const SUMMARY = 'summary';

    public const INSIST = 'insist';

    public function handle(ReviewQueue $queue, ReminderRegistry $registry, Notifier $notifier): int
    {
        $kind = $this->option('kind') ?? match (now()->dayOfWeekIso) {
            Carbon::MONDAY => self::SUMMARY,
            Carbon::THURSDAY => self::INSIST,
            default => null,
        };
        if (! in_array($kind, [self::SUMMARY, self::INSIST, null], true)) {
            $this->error('Tipo desconocido, usa summary o insist');

            return self::INVALID;
        }

        $dry = (bool) $this->option('dry-run');
        $insist = $kind === self::INSIST;
        $week = now()->format('o-\WW');
        $sent = 0;
        Activity::startRequest();

        foreach ($kind === null ? [] : $queue->reviewersWithQueue() as $user) {
            foreach ($queue->queuesOf($user) as $reviewQueue) {
                $pending = ReviewQueue::pending($reviewQueue);
                $late = (clone $pending)->where('updated_at', '<', now()->subDays(ReviewQueue::LATE_DAYS));
                $reminder = $this->reminder($reviewQueue, $pending, $late, $insist);
                if ($reminder === null) {
                    continue;
                }

                if ($dry) {
                    $this->line(ReviewQueueReminder::key().' · '.$kind.' · '.$reviewQueue['key'].' · '.$user->email.' · '.($insist ? $reminder->late : $reminder->pending));

                    continue;
                }

                if ($registry->claim($user, ReviewQueueReminder::key().':'.$kind.':'.$reviewQueue['key'].':'.$week)) {
                    $sent += $notifier->send([$user], $reminder);
                }
            }
        }

        if (! $dry) {
            $this->info(ReviewQueueReminder::key().' · '.$sent);
        }

        return self::SUCCESS;
    }

    /** The notice of a queue, or null when it has nothing to say (nothing pending, or nothing late on Thursday). */
    private function reminder(array $reviewQueue, Builder $pending, Builder $late, bool $insist): ?ReviewQueueReminder
    {
        $listed = $insist ? $late : $pending;
        $oldest = (clone $listed)->with('user')->reorder()->orderBy('updated_at')->orderBy('id')->limit(self::ITEMS)->get();
        if ($oldest->isEmpty()) {
            return null;
        }

        $days = fn (Evidence $evidence): int => (int) Carbon::parse($evidence->updated_at)->diffInDays(now());

        return new ReviewQueueReminder(
            kind: $reviewQueue['kind'],
            queueName: $reviewQueue['name'],
            pending: (clone $pending)->count(),
            late: (clone $late)->count(),
            oldestDays: $days($oldest->first()),
            items: $oldest->map(fn (Evidence $evidence): array => [
                'title' => (string) $evidence->title,
                'author' => trim($evidence->user?->name.' '.$evidence->user?->surname),
                'days' => $days($evidence),
            ])->values()->all(),
            insist: $insist,
        );
    }
}
