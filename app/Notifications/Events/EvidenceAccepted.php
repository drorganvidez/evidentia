<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\Evidence;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\AboutEvidence;
use Illuminate\Support\Collection;

/** An accepted evidence, to its author and its coauthors with the hours it adds. */
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
        $evidence = $activity->subject;
        $coauthors = $evidence instanceof Evidence ? $evidence->coauthors : collect();

        return collect([$activity->targetUser])->filter()->concat($coauthors)->unique('id')->values();
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

        if ($id !== null && $this->coauthoring($notifiable)) {
            return route('evidence.view', ['id' => $id]);
        }

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
        $whose = $this->coauthoring($notifiable) ? 'la evidencia en la que eres coautor' : 'tu evidencia';

        return [
            $this->actorName().' ha aceptado '.$whose.' «'.$this->evidenceTitle().'» del comité '.$this->committeeName().'.',
            'Suma '.self::hours($this->evidence()?->hours).' h a tu cómputo.',
        ];
    }

    private function coauthoring(User $notifiable): bool
    {
        $evidence = $this->evidence();

        return $evidence !== null && $evidence->user_id !== $notifiable->id && $evidence->hasCoauthor($notifiable);
    }

    public function mailAction(User $notifiable): string
    {
        return 'Ver la evidencia';
    }
}
