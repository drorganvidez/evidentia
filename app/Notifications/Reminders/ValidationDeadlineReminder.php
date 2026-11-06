<?php

namespace App\Notifications\Reminders;

use App\Enums\Deadline;
use App\Models\User;
use App\Services\ReviewQueue;
use Carbon\CarbonImmutable;

/**
 * The validation deadline, to whoever has evidences waiting in their review queue (App\Services\ReviewQueue): the
 * coordinators and the presidency.
 */
class ValidationDeadlineReminder extends DeadlineReminder
{
    /**
     * @param  list<string>  $items
     * @param  string  $kind  the queue the link opens, ReviewQueue::COORDINATION or ReviewQueue::PRESIDENCY
     */
    public function __construct(CarbonImmutable $closesAt, int $pending, array $items = [], ?string $committee = null, public string $kind = ReviewQueue::COORDINATION)
    {
        parent::__construct($closesAt, $pending, $items, $committee);
    }

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
        return ['COORDINATOR', 'PRESIDENT'];
    }

    public static function deadline(): Deadline
    {
        return Deadline::ValidateEvidences;
    }

    public function url(User $notifiable): string
    {
        return route(ReviewQueue::routes($this->kind)['pending']);
    }

    public function text(User $notifiable): ?string
    {
        return $this->pending.' '.($this->pending === 1 ? 'pendiente' : 'pendientes').' en '.$this->committee;
    }

    public function mailLines(User $notifiable): array
    {
        return [
            $this->closing('validación'),
            'La cola de '.$this->committee.' tiene '.self::quantity($this->pending, 'evidencia pendiente de revisión.', 'evidencias pendientes de revisión.'),
        ];
    }

    public function mailAction(User $notifiable): string
    {
        return 'Revisar pendientes';
    }
}
