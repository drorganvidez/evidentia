<?php

namespace App\Notifications\Concerns;

use App\Models\Evidence;

/**
 * The evidence of the annotation a notice comes from, with the copies of the log when the row is gone.
 */
trait AboutEvidence
{
    use FormatsNotices;

    protected function evidence(): ?Evidence
    {
        $subject = $this->activity?->subject;

        return $subject instanceof Evidence ? $subject : null;
    }

    protected function evidenceTitle(): string
    {
        return (string) ($this->activity?->subject_title ?? $this->evidence()?->title);
    }

    protected function committeeName(): string
    {
        return (string) ($this->evidence()?->committee?->name ?? $this->activity?->committee_name);
    }

    /** Whoever did the action, as «Nombre Apellidos». */
    protected function actorName(): string
    {
        return self::fullName($this->activity?->user, $this->activity?->user_name);
    }

    protected function evidenceId(): ?int
    {
        $id = $this->evidence()?->getKey() ?? $this->activity?->subject_id;

        return $id === null ? null : (int) $id;
    }
}
