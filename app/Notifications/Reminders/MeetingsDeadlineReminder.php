<?php

namespace App\Notifications\Reminders;

use App\Enums\Deadline;
use App\Models\User;

/** The meetings deadline, to the secretaries with meeting requests already held and still without minutes. */
class MeetingsDeadlineReminder extends DeadlineReminder
{
    public static function key(): string
    {
        return 'deadline.meetings';
    }

    public static function label(): string
    {
        return 'Recordatorio del plazo de reuniones';
    }

    public static function audience(): array
    {
        return ['SECRETARY'];
    }

    public static function deadline(): Deadline
    {
        return Deadline::Meetings;
    }

    public function text(User $notifiable): ?string
    {
        return $this->pending === 1 ? '1 convocatoria celebrada sin acta' : $this->pending.' convocatorias celebradas sin acta';
    }

    public function mailLines(User $notifiable): array
    {
        return [
            $this->closing('reuniones'),
            $this->pending === 1 ? 'Esta convocatoria celebrada aún no tiene acta.' : 'Estas convocatorias celebradas aún no tienen acta.',
        ];
    }

    public function mailAction(User $notifiable): string
    {
        return 'Gestionar actas';
    }
}
