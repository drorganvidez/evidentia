<?php

namespace App\Console\Commands;

use App\Enums\Deadline;
use App\Facades\Activity;
use App\Models\Evidence;
use App\Models\MeetingRequest;
use App\Models\User;
use App\Notifications\Reminders\DeadlineReminder;
use App\Notifications\Reminders\MeetingsDeadlineReminder;
use App\Notifications\Reminders\UploadDeadlineReminder;
use App\Notifications\Reminders\ValidationDeadlineReminder;
use App\Services\Deadlines;
use App\Services\Notifications\Notifier;
use App\Services\Notifications\ReminderRegistry;
use App\Services\ReviewQueue;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Reminds the upload, validation and meetings deadlines seven and two days before they close, once per person,
 * threshold and closing instant: a restart of the scheduler never sends one twice, a late run sends only the
 * nearest threshold and a deadline that moves is reminded again.
 */
final class SendDeadlineReminders extends Command
{
    protected $signature = 'notifications:remind {--dry-run : Lista los recordatorios sin enviarlos}';

    protected $description = 'Recuerda los plazos de subida, validación y reuniones a quien tiene trabajo pendiente';

    /** Days before the closing instant */
    public const THRESHOLDS = [2, 7];

    /** @var array<string, class-string<DeadlineReminder>> */
    private const REMINDERS = [
        Deadline::UploadEvidences->value => UploadDeadlineReminder::class,
        Deadline::ValidateEvidences->value => ValidationDeadlineReminder::class,
        Deadline::Meetings->value => MeetingsDeadlineReminder::class,
    ];

    public function handle(Deadlines $deadlines, ReminderRegistry $registry, Notifier $notifier): int
    {
        $dry = (bool) $this->option('dry-run');
        Activity::startRequest();

        foreach (self::REMINDERS as $deadline => $class) {
            $deadline = Deadline::from($deadline);
            $closesAt = $deadlines->closesAt($deadline);
            if ($closesAt === null || now()->gt($closesAt)) {
                continue;
            }

            $thresholds = array_values(array_filter(self::THRESHOLDS, fn (int $days): bool => now()->gte($closesAt->subDays($days))));
            if ($thresholds === []) {
                continue;
            }
            sort($thresholds);

            $sent = 0;
            foreach ($this->recipients($deadline, $closesAt, $class) as [$user, $reminder]) {
                if ($dry) {
                    $this->line($class::key().' · '.$user->email.' · '.$reminder->pending);

                    continue;
                }

                $claimed = $registry->claim($user, $this->key($class, $thresholds[0], $closesAt));
                // a late run must not send the seven-day reminder after the two-day one
                foreach (array_slice($thresholds, 1) as $days) {
                    $registry->claim($user, $this->key($class, $days, $closesAt));
                }

                if ($claimed) {
                    $sent += $notifier->send([$user], $reminder);
                }
            }

            if (! $dry) {
                $this->info($class::key().' · '.$sent);
            }
        }

        return self::SUCCESS;
    }

    /** deadline.upload:2:2026-06-15T20:00:00+02:00 */
    private function key(string $class, int $days, CarbonImmutable $closesAt): string
    {
        return $class::key().':'.$days.':'.$closesAt->toIso8601String();
    }

    /**
     * @param  class-string<DeadlineReminder>  $class
     * @return Collection<int, array{0: User, 1: DeadlineReminder}>
     */
    private function recipients(Deadline $deadline, CarbonImmutable $closesAt, string $class): Collection
    {
        return match ($deadline) {
            Deadline::UploadEvidences => $this->authors($closesAt, $class),
            Deadline::ValidateEvidences => $this->coordinators($closesAt, $class),
            Deadline::Meetings => $this->secretaries($closesAt, $class),
            default => collect(),
        };
    }

    /** Whoever has a last version in draft or rejected. */
    private function authors(CarbonImmutable $closesAt, string $class): Collection
    {
        return Evidence::query()
            ->where('last', true)
            ->whereIn('status', ['DRAFT', 'REJECTED'])
            ->orderBy('title')
            ->get(['id', 'title', 'user_id'])
            ->groupBy('user_id')
            ->map(fn (Collection $evidences, int $userId): ?array => ($user = User::find($userId)) === null ? null : [
                $user,
                new $class($closesAt, $evidences->count(), $evidences->pluck('title')->map(fn ($title): string => (string) $title)->values()->all()),
            ])
            ->filter()
            ->values();
    }

    /** Whoever has evidences waiting in their review queues, the coordinators and the presidency (App\Services\ReviewQueue). */
    private function coordinators(CarbonImmutable $closesAt, string $class): Collection
    {
        $queue = app(ReviewQueue::class);

        return $queue->reviewersWithQueue()
            ->map(function (User $user) use ($queue, $closesAt): ?array {
                $queues = collect($queue->queuesOf($user))
                    ->map(fn (array $reviewQueue): array => $reviewQueue + ['count' => ReviewQueue::pending($reviewQueue)->count()])
                    ->filter(fn (array $reviewQueue): bool => $reviewQueue['count'] > 0)
                    ->values();

                return $queues->isEmpty() ? null : [$user, new ValidationDeadlineReminder(
                    $closesAt,
                    (int) $queues->sum('count'),
                    [],
                    $queues->pluck('name')->join(', ', ' y '),
                    $queues->first()['kind'],
                )];
            })
            ->filter()
            ->values();
    }

    /** The secretaries with requests of their own already held and without minutes. */
    private function secretaries(CarbonImmutable $closesAt, string $class): Collection
    {
        return User::query()
            ->whereHas('roles', fn (Builder $roles) => $roles->where('rol', 'SECRETARY'))
            ->whereHas('secretary')
            ->with(['secretary', 'roles'])
            ->get()
            ->map(function (User $user) use ($closesAt, $class): ?array {
                $requests = MeetingRequest::query()
                    ->where('secretary_id', $user->secretary->id)
                    ->where('datetime', '<', now())
                    ->whereNull('meeting_id')
                    ->orderBy('datetime')
                    ->get(['title', 'datetime']);

                return $requests->isEmpty() ? null : [$user, new $class($closesAt, $requests->count(), $requests
                    ->map(fn (MeetingRequest $request): string => $request->title.' · '.CarbonImmutable::parse($request->datetime)->format('d/m/Y'))
                    ->values()->all())];
            })
            ->filter()
            ->values();
    }
}
