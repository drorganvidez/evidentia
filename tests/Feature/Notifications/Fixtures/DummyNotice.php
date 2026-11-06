<?php

namespace Tests\Feature\Notifications\Fixtures;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\BaseNotification;
use Illuminate\Support\Collection;

/**
 * Important notice of the tests, sent by evidence.accepted to the affected people and to the actor (whom the
 * notifier must leave out).
 */
class DummyNotice extends BaseNotification
{
    public static function key(): string
    {
        return 'test.important';
    }

    public static function label(): string
    {
        return 'Aviso importante de prueba';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Important;
    }

    public static function triggers(): array
    {
        return [ActivityAction::EvidenceAccepted];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        $ids = [$activity->user_id, $activity->target_user_id, ...$activity->targets()->pluck('user_id')->all()];

        return User::whereIn('id', array_filter($ids))->get();
    }

    public function title(User $notifiable): string
    {
        return 'Evidencia aceptada';
    }

    public function text(User $notifiable): ?string
    {
        return $this->activity?->subject_title;
    }

    public function url(User $notifiable): string
    {
        return route('home', ['from' => 'aviso']);
    }

    public function tone(): string
    {
        return 'accepted';
    }

    public function mailSubject(User $notifiable): string
    {
        return 'Evidencia aceptada · '.$this->activity?->subject_title;
    }

    public function mailLines(User $notifiable): array
    {
        return ['Han aceptado tu evidencia.'];
    }

    public function mailAction(User $notifiable): string
    {
        return 'Ver la evidencia';
    }
}
