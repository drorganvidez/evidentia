<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\Deadline;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\Evidence;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\AboutEvidence;
use App\Notifications\Digestible;
use App\Notifications\Recipients;
use App\Services\Deadlines;
use Illuminate\Support\Collection;

/**
 * An evidence sent for review, to the coordinators and secretaries of its committee. When no coordinator but the
 * author is left to review it, the management gets it too.
 */
class EvidenceSubmitted extends BaseNotification implements Digestible
{
    use AboutEvidence;

    public static function key(): string
    {
        return 'evidence.submitted';
    }

    public static function label(): string
    {
        return 'Evidencia recibida en tu comité';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Important;
    }

    public static function audience(): array
    {
        return ['COORDINATOR', 'SECRETARY', 'PRESIDENT', 'LECTURE'];
    }

    public static function triggers(): array
    {
        return [ActivityAction::EvidencePublished];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        $evidence = $activity->subject;
        // the president publishes straight to accepted: nothing to review
        if (($activity->details['status'] ?? null) !== 'PENDING' || ! $evidence instanceof Evidence || $evidence->committee_id === null) {
            return collect();
        }

        $author = (int) $evidence->user_id;
        $reviewers = Recipients::coordinators((int) $evidence->committee_id, except: $author);
        $people = $reviewers->concat(Recipients::secretaries((int) $evidence->committee_id));

        return ($reviewers->isEmpty() ? $people->concat(Recipients::managers()) : $people)
            ->unique(fn (User $user) => $user->getKey())
            ->values();
    }

    public function title(User $notifiable): string
    {
        return 'Evidencia recibida en '.$this->committeeName();
    }

    public function text(User $notifiable): ?string
    {
        return $this->activity?->user_name.' ha enviado «'.$this->evidenceTitle().'» ('.self::hours($this->hoursValue()).' h)';
    }

    public function url(User $notifiable): string
    {
        $evidence = $this->evidence();

        return $evidence === null ? route('home') : self::evidenceLinkFor($notifiable, $evidence);
    }

    public function tone(): string
    {
        return 'pending';
    }

    public function mailSubject(User $notifiable): string
    {
        return 'Evidencia recibida en '.$this->committeeName().' · '.$this->evidenceTitle();
    }

    public function mailLines(User $notifiable): array
    {
        $lines = [$this->actorName().' ha enviado la evidencia «'.$this->evidenceTitle().'» ('.self::hours($this->hoursValue()).' h) al comité '.$this->committeeName().'.'];

        $closesAt = app(Deadlines::class)->closesAt(Deadline::ValidateEvidences);
        if ($closesAt !== null) {
            $lines[] = 'El plazo de validación cierra el '.self::day($closesAt).' a las '.self::clock($closesAt).'.';
        }

        if ($this->isBackup($notifiable)) {
            $lines[] = 'Este comité no tiene otro coordinador que pueda revisarla.';
        }

        return $lines;
    }

    public function mailAction(User $notifiable): string
    {
        return 'Revisar la evidencia';
    }

    /** The inbox row also keeps the line of the daily digest, which reads it back. */
    public function toArray(object $notifiable): array
    {
        return parent::toArray($notifiable) + [
            'digest' => $this->evidenceTitle().' · '.$this->actorName().' · '.self::hours($this->hoursValue()).' h',
        ];
    }

    private function hoursValue(): float
    {
        return (float) ($this->activity?->details['hours'] ?? $this->evidence()?->hours ?? 0);
    }

    /** The management only gets it when the committee has nobody else to review it. */
    private function isBackup(User $notifiable): bool
    {
        $committee = $this->evidence()?->committee_id;

        return ($notifiable->hasRole('PRESIDENT') || $notifiable->hasRole('LECTURE'))
            && ! ($notifiable->hasRole('COORDINATOR') && (int) $notifiable->coordinator?->committee_id === (int) $committee)
            && ! ($notifiable->hasRole('SECRETARY') && (int) $notifiable->secretary?->committee_id === (int) $committee);
    }
}
