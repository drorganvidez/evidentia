<?php

namespace Tests\Feature\Mail\Fixtures;

use App\Enums\NotificationLevel;
use App\Models\User;
use App\Notifications\BaseNotification;

/** A fixed notice of the tests with every part of the mail: lines, a quoted text and a list. */
class ThemedNotice extends BaseNotification
{
    /** @param  list<string>  $items */
    public function __construct(public string $heading, public ?string $panel = null, public array $items = [])
    {
        parent::__construct();
    }

    public static function key(): string
    {
        return 'test.themed';
    }

    public static function label(): string
    {
        return 'Aviso con tema de prueba';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Fixed;
    }

    public function title(User $notifiable): string
    {
        return $this->heading;
    }

    public function text(User $notifiable): ?string
    {
        return null;
    }

    public function url(User $notifiable): string
    {
        return route('home');
    }

    public function tone(): string
    {
        return 'rejected';
    }

    public function mailSubject(User $notifiable): string
    {
        return 'Evidencia rechazada · '.$this->heading;
    }

    public function mailLines(User $notifiable): array
    {
        return ['Carlos Navarro Ruiz ha rechazado tu evidencia «'.$this->heading.'» del comité Logística.'];
    }

    public function mailPanel(User $notifiable): ?string
    {
        return $this->panel;
    }

    public function mailItems(User $notifiable): array
    {
        return $this->items;
    }

    public function mailAction(User $notifiable): string
    {
        return 'Ver la evidencia';
    }
}
