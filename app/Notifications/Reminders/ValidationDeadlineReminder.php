<?php

namespace App\Notifications\Reminders;

use App\Enums\Deadline;
use App\Models\User;

/** The validation deadline, to the coordinators whose committee has evidences of others waiting for review. */
class ValidationDeadlineReminder extends DeadlineReminder
{
    public static function key(): string
    {
        return 'deadline.validate';
    }

    public static function label(): string
    {
        return 'Recordatorio del plazo de validación';
    }

    public static function audience(): array
    {
        return ['COORDINATOR'];
    }

    public static function deadline(): Deadline
    {
        return Deadline::ValidateEvidences;
    }

    public function text(User $notifiable): ?string
    {
        return $this->pending.' '.($this->pending === 1 ? 'pendiente' : 'pendientes').' en '.$this->committee;
    }

    public function mailLines(User $notifiable): array
    {
        return [
            $this->closing('validación'),
            'El comité '.$this->committee.' tiene '.self::quantity($this->pending, 'evidencia pendiente de revisión.', 'evidencias pendientes de revisión.'),
        ];
    }

    public function mailAction(User $notifiable): string
    {
        return 'Revisar pendientes';
    }
}
