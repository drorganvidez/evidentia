<?php

namespace App\Notifications\Reminders;

use App\Enums\Deadline;
use App\Enums\NotificationLevel;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\FormatsNotices;
use Carbon\CarbonImmutable;

/**
 * A deadline about to close, to whoever still has work under it. Always by mail. The days left are counted when it is
 * sent, not taken from the threshold that triggered it.
 */
abstract class DeadlineReminder extends BaseNotification
{
    use FormatsNotices;

    /** @param  list<string>  $items */
    public function __construct(public CarbonImmutable $closesAt, public int $pending, public array $items = [], public ?string $committee = null)
    {
        parent::__construct();
    }

    abstract public static function deadline(): Deadline;

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Fixed;
    }

    /** «Quedan 7 días para subir evidencias», «Queda un día…», «Último día…» */
    public function title(User $notifiable): string
    {
        $days = (int) now()->startOfDay()->diffInDays($this->closesAt->startOfDay(), false);
        $verb = static::deadline()->verb();

        return match (true) {
            $days <= 0 => 'Último día '.$verb,
            $days === 1 => 'Queda un día '.$verb,
            default => 'Quedan '.$days.' días '.$verb,
        };
    }

    public function url(User $notifiable): string
    {
        return route(static::deadline()->listRoute());
    }

    public function tone(): string
    {
        return 'pending';
    }

    public function mailSubject(User $notifiable): string
    {
        return $this->title($notifiable);
    }

    public function mailItems(User $notifiable): array
    {
        return $this->items;
    }

    /** «El plazo de subida cierra el 15/06/2026 a las 20:00.» */
    protected function closing(string $name): string
    {
        return 'El plazo de '.$name.' cierra el '.self::day($this->closesAt).' a las '.self::clock($this->closesAt).'.';
    }
}
