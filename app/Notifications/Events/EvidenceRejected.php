<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\Deadline;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\AboutEvidence;
use App\Services\Deadlines;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** A rejected evidence, to its author with the reason and the time left to correct it. Always by mail. */
class EvidenceRejected extends BaseNotification
{
    use AboutEvidence;

    public static function key(): string
    {
        return 'evidence.rejected';
    }

    public static function label(): string
    {
        return 'Evidencia rechazada';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Fixed;
    }

    public static function triggers(): array
    {
        return [ActivityAction::EvidenceRejected];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        return collect([$activity->targetUser])->filter()->values();
    }

    public function title(User $notifiable): string
    {
        return 'Evidencia rechazada';
    }

    public function text(User $notifiable): ?string
    {
        return $this->evidenceTitle().' · '.Str::limit($this->reason(), 120);
    }

    public function url(User $notifiable): string
    {
        $id = $this->evidenceId();

        return $id === null ? route('evidence.list') : self::authorLink($id);
    }

    public function tone(): string
    {
        return 'rejected';
    }

    public function mailSubject(User $notifiable): string
    {
        return 'Evidencia rechazada · '.$this->evidenceTitle();
    }

    public function mailLines(User $notifiable): array
    {
        $lines = [$this->actorName().' ha rechazado tu evidencia «'.$this->evidenceTitle().'» del comité '.$this->committeeName().'.'];

        $deadlines = app(Deadlines::class);
        $closesAt = $deadlines->closesAt(Deadline::UploadEvidences);
        if (! $deadlines->isOpen(Deadline::UploadEvidences)) {
            $lines[] = 'El plazo de subida está cerrado. Si crees que es un error, escribe a tu coordinador desde la evidencia.';
        } elseif ($closesAt !== null) {
            $lines[] = 'Tienes hasta el '.self::day($closesAt).' a las '.self::clock($closesAt).' para corregirla y volver a enviarla ('
                .$deadlines->remainingText(Deadline::UploadEvidences).').';
        }

        return $lines;
    }

    public function mailPanel(User $notifiable): ?string
    {
        return $this->reason() === '' ? null : $this->reason();
    }

    public function mailAction(User $notifiable): string
    {
        return 'Ver la evidencia';
    }

    private function reason(): string
    {
        return trim((string) ($this->activity?->details['reason'] ?? ''));
    }
}
