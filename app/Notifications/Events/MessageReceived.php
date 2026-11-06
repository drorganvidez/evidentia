<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\MessageThread;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\AboutAThread;
use App\Support\Roles;
use Illuminate\Support\Collection;

/** A new thread, to the members of its audience. */
class MessageReceived extends BaseNotification
{
    use AboutAThread;

    public static function key(): string
    {
        return 'message.received';
    }

    public static function label(): string
    {
        return 'Mensaje nuevo';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Important;
    }

    public static function audience(): array
    {
        return Roles::RESPONSIBLE;
    }

    public static function triggers(): array
    {
        return [ActivityAction::MessageSent];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        $thread = $activity->subject;

        return $thread instanceof MessageThread ? $thread->audience()->members() : collect();
    }

    public function title(User $notifiable): string
    {
        return 'Mensaje nuevo de '.$this->actor();
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
        return 'Mensaje nuevo · '.$this->threadSubject();
    }

    public function mailLines(User $notifiable): array
    {
        $thread = $this->thread();
        $role = Roles::asLabel($this->activity?->acting_role);
        $lines = [trim($this->actor().' escribe a '.($thread?->audienceLabel() ?? 'Evidentia').' '.($role ?? '')).'.'];
        if (filled($thread?->about_title)) {
            $lines[] = 'Sobre «'.$thread->about_title.'».';
        }

        return $lines;
    }

    public function mailPanel(User $notifiable): ?string
    {
        return self::excerpt($this->thread()?->messages()->value('body'));
    }
}
