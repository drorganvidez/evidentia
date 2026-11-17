<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\AboutEvidence;
use Illuminate\Support\Collection;

/** A coauthor leaving a pending or accepted evidence, to its author. */
class EvidenceCoauthorLeft extends BaseNotification
{
    use AboutEvidence;

    public static function key(): string
    {
        return 'evidence.coauthor_left';
    }

    public static function label(): string
    {
        return 'Baja de un coautor en una evidencia';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Important;
    }

    public static function audience(): array
    {
        return ['STUDENT'];
    }

    public static function triggers(): array
    {
        return [ActivityAction::EvidenceCoauthorLeft];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        return collect([$activity->targetUser])->filter()->values();
    }

    public function title(User $notifiable): string
    {
        return 'Un coautor ha dejado tu evidencia';
    }

    public function text(User $notifiable): ?string
    {
        return $this->actorName().' · '.$this->evidenceTitle();
    }

    public function url(User $notifiable): string
    {
        $id = $this->evidenceId();

        return $id === null ? route('evidence.list') : self::authorLink($id);
    }

    public function tone(): string
    {
        return 'notice';
    }

    public function mailSubject(User $notifiable): string
    {
        return 'Coautoría abandonada · '.$this->evidenceTitle();
    }

    public function mailLines(User $notifiable): array
    {
        return [
            $this->actorName().' ha dejado de figurar como coautor en tu evidencia «'.$this->evidenceTitle().'» del comité '.$this->committeeName().'.',
        ];
    }

    public function mailAction(User $notifiable): string
    {
        return 'Ver la evidencia';
    }
}
