<?php

namespace App\Notifications\Reminders;

use App\Enums\Deadline;
use App\Enums\NotificationLevel;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\FormatsNotices;
use App\Services\Deadlines;
use App\Services\ReviewQueue;

/**
 * The weekly summary of a review queue (App\Services\ReviewQueue): every Monday to whoever still has evidences waiting
 * in it, and again on Thursday when some have waited more than a week. Fixed, so nobody can turn it off.
 */
class ReviewQueueReminder extends BaseNotification
{
    use FormatsNotices;

    /**
     * @param  string  $kind  ReviewQueue::COORDINATION or ReviewQueue::PRESIDENCY
     * @param  string  $queueName  name of the queue, its committee
     * @param  int  $late  pending for more than ReviewQueue::LATE_DAYS days
     * @param  list<array{title: string, author: string, days: int}>  $items  the oldest ones, the oldest first
     * @param  bool  $insist  the reminder of Thursday, about the late ones
     */
    public function __construct(
        public string $kind,
        public string $queueName,
        public int $pending,
        public int $late,
        public int $oldestDays,
        public array $items = [],
        public bool $insist = false,
    ) {
        parent::__construct();
    }

    public static function key(): string
    {
        return 'review.weekly';
    }

    public static function label(): string
    {
        return 'Resumen semanal de evidencias por revisar';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Fixed;
    }

    public static function audience(): array
    {
        return ['COORDINATOR', 'PRESIDENT'];
    }

    public function title(User $notifiable): string
    {
        return $this->insist
            ? self::quantity($this->late, 'evidencia espera', 'evidencias esperan').' más de '.ReviewQueue::LATE_DAYS.' días'
            : 'Resumen semanal · '.self::quantity($this->pending, 'evidencia por revisar', 'evidencias por revisar');
    }

    public function text(User $notifiable): ?string
    {
        return 'Cola de '.$this->queueName.' · '.$this->waiting($this->oldestDays);
    }

    public function url(User $notifiable): string
    {
        return route(ReviewQueue::routes($this->kind)['pending']);
    }

    public function tone(): string
    {
        return $this->insist ? 'rejected' : 'pending';
    }

    public function mailSubject(User $notifiable): string
    {
        return $this->title($notifiable).' en '.$this->queueName;
    }

    public function mailLines(User $notifiable): array
    {
        $lines = [
            $this->insist
                ? 'A mitad de semana siguen en la cola de '.$this->queueName.' '.self::quantity($this->late, 'evidencia', 'evidencias').' de hace más de '.ReviewQueue::LATE_DAYS.' días.'
                : 'Tienes '.self::quantity($this->pending, 'evidencia pendiente', 'evidencias pendientes').' de revisar en la cola de '.$this->queueName.'.',
            ucfirst($this->waiting($this->oldestDays)).'.',
        ];

        $closesAt = app(Deadlines::class)->closesAt(Deadline::ValidateEvidences);
        if ($closesAt !== null && now()->lt($closesAt)) {
            $lines[] = 'El plazo de validación cierra el '.self::day($closesAt).' a las '.self::clock($closesAt).'.';
        }

        return $lines;
    }

    /** «Montaje del photocall · Lucía Pérez Romero · 9 días en cola» */
    public function mailItems(User $notifiable): array
    {
        return array_map(
            fn (array $item): string => $item['title'].' · '.$item['author'].' · '.($item['days'] === 0 ? 'de hoy' : self::quantity($item['days'], 'día', 'días').' en cola'),
            $this->items,
        );
    }

    public function mailAction(User $notifiable): string
    {
        return 'Revisar pendientes';
    }

    /** «la más antigua espera 9 días», «la más antigua es de hoy» */
    private function waiting(int $days): string
    {
        return $days === 0 ? 'la más antigua es de hoy' : 'la más antigua espera '.self::quantity($days, 'día', 'días');
    }
}
