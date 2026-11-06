<?php

namespace App\Notifications\Concerns;

use App\Models\ActivityLog;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Shared pieces of the notices of a message thread: the thread of the annotation, its link, who wrote and the
 * participants it reaches.
 */
trait AboutAThread
{
    public function thread(): ?MessageThread
    {
        $subject = $this->activity?->subject;

        return $subject instanceof MessageThread ? $subject : null;
    }

    public function url(User $notifiable): string
    {
        $id = $this->activity?->subject_id;

        return $id === null ? route('messages.mine') : route('messages.show', ['thread' => $id]);
    }

    public function mailAction(User $notifiable): string
    {
        return 'Abrir el mensaje';
    }

    /** Subject of the thread, the copied one if the thread is gone. */
    protected function threadSubject(): string
    {
        return (string) ($this->thread()?->subject ?? $this->activity?->subject_title);
    }

    /** Who acted, «Nombre Apellidos», or the name copied in the annotation if the person is gone. */
    protected function actor(): string
    {
        $user = $this->activity?->user;

        return $user ? trim($user->name.' '.$user->surname) : (string) ($this->activity?->user_name ?? 'Evidentia');
    }

    /** The first letters of a message for the panel of the mail. */
    protected static function excerpt(?string $body): ?string
    {
        return $body === null ? null : Str::limit($body, 500);
    }

    /**
     * The author, whoever wrote in the thread and its audience; the notifier leaves out whoever acted.
     *
     * @return Collection<int, User>
     */
    protected static function participantsOf(ActivityLog $activity): Collection
    {
        $thread = $activity->subject;

        return $thread instanceof MessageThread ? $thread->participants() : collect();
    }
}
