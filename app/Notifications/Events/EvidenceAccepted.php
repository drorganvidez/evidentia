<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\AboutEvidence;
use Illuminate\Support\Collection;

/** An accepted evidence, to its author with the hours it adds. */
class EvidenceAccepted extends BaseNotification
{
    use AboutEvidence;

    public static function key(): string
    {
        return 'evidence.accepted';
    }

    public static function label(): string
    {
        return 'Evidencia aceptada';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Important;
    }

    public static function triggers(): array
    {
        return [ActivityAction::EvidenceAccepted];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        return collect([$activity->targetUser])->filter()->values();
    }

    public function title(User $notifiable): string
    {
        return 'Evidencia aceptada';
    }

    public function text(User $notifiable): ?string
    {
        return $this->evidenceTitle().' suma '.self::hours($this->evidence()?->hours).' h';
    }

    public function url(User $notifiable): string
    {
        $id = $this->evidenceId();

        return $id === null ? route('evidence.list') : self::authorLink($id);
    }

    public function tone(): string
    {
        return 'accepted';
    }

    public function mailSubject(User $notifiable): string
    {
        return 'Evidencia aceptada · '.$this->evidenceTitle();
    }

    public function mailLines(User $notifiable): array
    {
        return [
            $this->actorName().' ha aceptado tu evidencia «'.$this->evidenceTitle().'» del comité '.$this->committeeName().'.',
            'Suma '.self::hours($this->evidence()?->hours).' h a tu cómputo.',
        ];
    }

    public function mailAction(User $notifiable): string
    {
        return 'Ver la evidencia';
    }
}
