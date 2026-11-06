<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\Message;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\AboutAThread;
use Illuminate\Support\Collection;

/** An answer in a thread, to its author, its audience and whoever wrote in it. */
class MessageReplied extends BaseNotification
{
    use AboutAThread;

    public static function key(): string
    {
        return 'message.replied';
    }

    public static function label(): string
    {
        return 'Respuesta en un hilo';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Important;
    }

    public static function triggers(): array
    {
        return [ActivityAction::MessageReplied];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        return self::participantsOf($activity);
    }

    public function title(User $notifiable): string
    {
        return 'Respuesta de '.$this->actor();
    }

    public function text(User $notifiable): ?string
    {
        return $this->threadSubject();
    }

    public function tone(): string
    {
        return 'pending';
    }

    public function mailSubject(User $notifiable): string
    {
        return 'Respuesta · '.$this->threadSubject();
    }

    public function mailLines(User $notifiable): array
    {
        return [$this->actor().' ha respondido en el hilo «'.$this->threadSubject().'».'];
    }

    public function mailPanel(User $notifiable): ?string
    {
        $id = $this->activity?->details['message_id'] ?? null;

        return self::excerpt(is_numeric($id) ? Message::whereKey((int) $id)->value('body') : null);
    }
}
