<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\Evidence;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\AboutEvidence;
use App\Notifications\Recipients;
use Illuminate\Support\Collection;

/**
 * An evidence moved to another committee: to its author when someone else moved it, and to the coordinators of the
 * new committee when it is waiting for review.
 */
class EvidenceMoved extends BaseNotification
{
    use AboutEvidence;

    public static function key(): string
    {
        return 'evidence.moved';
    }

    public static function label(): string
    {
        return 'Evidencia movida de comité';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Important;
    }

    public static function triggers(): array
    {
        return [ActivityAction::EvidenceMoved];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        $people = collect([$activity->targetUser])->filter();
        $evidence = $activity->subject;
        $to = $activity->details['after']['committee_id'] ?? null;

        if (($activity->details['status'] ?? null) === 'PENDING' && $to !== null && $evidence instanceof Evidence) {
            $people = $people->concat(Recipients::coordinators((int) $to, except: (int) $evidence->user_id));
        }

        return $people->unique(fn (User $user) => $user->getKey())->values();
    }

    public function title(User $notifiable): string
    {
        return $this->isAuthor($notifiable) ? 'Evidencia movida a '.$this->to() : 'Evidencia recibida en '.$this->to();
    }

    public function text(User $notifiable): ?string
    {
        return $this->isAuthor($notifiable)
            ? $this->evidenceTitle().' pasa de '.$this->from().' a '.$this->to()
            : $this->authorName().' · '.$this->evidenceTitle().', movida desde '.$this->from();
    }

    public function url(User $notifiable): string
    {
        $evidence = $this->evidence();
        if ($evidence === null) {
            return route('home');
        }

        return $this->isAuthor($notifiable) ? self::authorLink($evidence) : self::evidenceLinkFor($notifiable, $evidence);
    }

    public function tone(): string
    {
        return 'notice';
    }

    public function mailSubject(User $notifiable): string
    {
        return $this->title($notifiable).' · '.$this->evidenceTitle();
    }

    public function mailLines(User $notifiable): array
    {
        if ($this->isAuthor($notifiable)) {
            return [
                $this->actorName().' ha movido tu evidencia «'.$this->evidenceTitle().'» del comité '.$this->from().' al comité '.$this->to().'.',
                'Conserva sus pruebas, su descripción y su estado.',
            ];
        }

        return [
            $this->actorName().' ha movido al comité '.$this->to().' la evidencia «'.$this->evidenceTitle().'» de '.$this->authorName().'.',
            'Está pendiente de revisión.',
        ];
    }

    public function mailAction(User $notifiable): string
    {
        return $this->isAuthor($notifiable) ? 'Ver la evidencia' : 'Revisar la evidencia';
    }

    private function isAuthor(User $notifiable): bool
    {
        $author = $this->evidence()?->user_id ?? $this->activity?->target_user_id;

        return (int) $notifiable->getKey() === (int) $author;
    }

    private function authorName(): string
    {
        return self::fullName($this->evidence()?->user, $this->activity?->target_name);
    }

    private function from(): string
    {
        return (string) ($this->activity?->details['before']['committee_name'] ?? '');
    }

    private function to(): string
    {
        return (string) ($this->activity?->details['after']['committee_name'] ?? $this->committeeName());
    }
}
