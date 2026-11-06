<?php

namespace App\Notifications\Reminders;

use App\Enums\Deadline;
use App\Models\User;

/** The upload deadline, to whoever has evidences in draft or rejected and not corrected. */
class UploadDeadlineReminder extends DeadlineReminder
{
    public static function key(): string
    {
        return 'deadline.upload';
    }

    public static function label(): string
    {
        return 'Recordatorio del plazo de subida';
    }

    public static function audience(): array
    {
        return ['STUDENT', 'COORDINATOR', 'SECRETARY', 'REGISTER_COORDINATOR', 'PRESIDENT'];
    }

    public static function deadline(): Deadline
    {
        return Deadline::UploadEvidences;
    }

    public function text(User $notifiable): ?string
    {
        return $this->pending === 1
            ? '1 evidencia en borrador o rechazada sin corregir'
            : $this->pending.' evidencias en borrador o rechazadas sin corregir';
    }

    public function mailLines(User $notifiable): array
    {
        return [
            $this->closing('subida'),
            'Tienes '.self::quantity($this->pending, 'evidencia en borrador o rechazada sin corregir.', 'evidencias en borrador o rechazadas sin corregir.'),
        ];
    }

    public function mailAction(User $notifiable): string
    {
        return 'Ver mis evidencias';
    }
}
