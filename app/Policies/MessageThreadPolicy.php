<?php

namespace App\Policies;

use App\Models\MessageThread;
use App\Models\User;

/**
 * Who sees and acts on a thread: its author, the members of its audience and the lecturers, who see every thread.
 * Only an open thread is answered; the author never resolves it, but may reopen it.
 */
final class MessageThreadPolicy
{
    public function view(User $user, MessageThread $thread): bool
    {
        return $this->isAuthor($user, $thread) || $this->isInCharge($user, $thread);
    }

    public function reply(User $user, MessageThread $thread): bool
    {
        return $this->view($user, $thread) && ! $thread->isResolved();
    }

    public function resolve(User $user, MessageThread $thread): bool
    {
        return ! $this->isAuthor($user, $thread) && $this->isInCharge($user, $thread) && ! $thread->isResolved();
    }

    public function reopen(User $user, MessageThread $thread): bool
    {
        return $this->view($user, $thread) && $thread->isResolved();
    }

    private function isAuthor(User $user, MessageThread $thread): bool
    {
        return (int) $thread->author_id === (int) $user->getKey();
    }

    private function isInCharge(User $user, MessageThread $thread): bool
    {
        return $user->hasRole('LECTURE') || $thread->audience()->includes($user);
    }
}
