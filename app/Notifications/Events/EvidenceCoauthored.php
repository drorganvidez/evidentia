<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\AboutEvidence;
use Illuminate\Support\Collection;

/** An evidence sent for review that names coauthors, to each of them: its hours will count for them too. */
class EvidenceCoauthored extends BaseNotification
{
    use AboutEvidence;

    public static function key(): string
    {
        return 'evidence.coauthored';
    }

    public static function label(): string
    {
        return 'Coautoría en una evidencia';
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
        return [ActivityAction::EvidencePublished];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        $ids = array_filter((array) ($activity->details['coauthor_ids'] ?? []), 'is_int');

        return $ids === [] ? collect() : User::whereIn('id', $ids)->whereKeyNot($activity->user_id)->get();
    }

    public function title(User $notifiable): string
    {
        return 'Te han añadido como coautor';
    }

    public function text(User $notifiable): ?string
    {
        return $this->actorName().' · '.$this->evidenceTitle();
    }

    public function url(User $notifiable): string
    {
        $id = $this->evidenceId();

        return $id === null ? route('evidence.list') : route('evidence.view', ['id' => $id]);
    }

    public function tone(): string
    {
        return 'notice';
    }

    public function mailSubject(User $notifiable): string
    {
        return 'Coautoría · '.$this->evidenceTitle();
    }

    public function mailLines(User $notifiable): array
    {
        return [
            $this->actorName().' ha enviado a revisión la evidencia «'.$this->evidenceTitle().'» del comité '.$this->committeeName().' y figuras en ella como coautor.',
            'Si se acepta, sus '.self::hours($this->evidence()?->hours).' h se sumarán también a tu cómputo.',
        ];
    }

    public function mailAction(User $notifiable): string
    {
        return 'Ver la evidencia';
    }
}
