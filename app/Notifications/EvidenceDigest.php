<?php

namespace App\Notifications;

use App\Enums\NotificationLevel;
use App\Models\User;
use App\Notifications\Concerns\FormatsNotices;

/**
 * The evidences sent to the committee since the last digest, in one mail a day for the coordinators and secretaries
 * who chose it. Only by mail (the inbox already has each one) and outside the preferences and the catalogue.
 */
class EvidenceDigest extends BaseNotification
{
    use FormatsNotices;

    /** @param  list<string>  $items  «título · autor · h h» */
    public function __construct(public array $items)
    {
        parent::__construct();
    }

    public static function key(): string
    {
        return 'evidence.digest';
    }

    public static function label(): string
    {
        return 'Resumen diario';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Important;
    }

    public static function audience(): array
    {
        return ['COORDINATOR', 'SECRETARY'];
    }

    public static function inPreferences(): bool
    {
        return false;
    }

    /** Only the mail; a blocked person gets nothing, the inbox already holds every evidence. */
    public function via(object $notifiable): array
    {
        return $this->channels = $notifiable instanceof User && $notifiable->block ? [] : ['mail'];
    }

    public function title(User $notifiable): string
    {
        return 'Resumen del día · '.self::quantity(count($this->items), 'evidencia recibida', 'evidencias recibidas');
    }

    public function text(User $notifiable): ?string
    {
        return null;
    }

    public function url(User $notifiable): string
    {
        return $notifiable->hasRole('COORDINATOR') && $notifiable->coordinator !== null
            ? route('coordinator.evidence.list.pending')
            : self::link('activity.index', [], 'home');
    }

    public function tone(): string
    {
        return 'pending';
    }

    public function mailSubject(User $notifiable): string
    {
        return $this->title($notifiable);
    }

    public function mailLines(User $notifiable): array
    {
        return ['Evidencias enviadas a tu comité desde el último resumen.'];
    }

    public function mailItems(User $notifiable): array
    {
        return $this->items;
    }

    public function mailAction(User $notifiable): string
    {
        return 'Revisar pendientes';
    }
}
