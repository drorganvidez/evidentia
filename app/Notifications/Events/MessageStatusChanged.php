<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\AboutAThread;
use Illuminate\Support\Collection;

/** A thread resolved or reopened, to its author, its audience and whoever wrote in it. */
class MessageStatusChanged extends BaseNotification
{
    use AboutAThread;

    public static function key(): string
    {
        return 'message.status';
    }

    public static function label(): string
    {
        return 'Hilo resuelto o reabierto';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Informative;
    }

    public static function triggers(): array
    {
        return [ActivityAction::MessageResolved, ActivityAction::MessageReopened];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        return self::participantsOf($activity);
    }

    public function title(User $notifiable): string
    {
        return $this->resolved() ? 'Hilo resuelto' : 'Hilo reabierto';
    }

    public function text(User $notifiable): ?string
    {
        return $this->threadSubject();
    }

    public function tone(): string
    {
        return $this->resolved() ? 'accepted' : 'notice';
    }

    public function mailSubject(User $notifiable): string
    {
        return $this->title($notifiable).' · '.$this->threadSubject();
    }

    public function mailLines(User $notifiable): array
    {
        return [$this->resolved()
            ? $this->actor().' ha marcado como resuelto el hilo «'.$this->threadSubject().'».'
            : $this->actor().' ha reabierto el hilo «'.$this->threadSubject().'».'];
    }

    private function resolved(): bool
    {
        return $this->activity?->action === ActivityAction::MessageResolved->value;
    }
}
