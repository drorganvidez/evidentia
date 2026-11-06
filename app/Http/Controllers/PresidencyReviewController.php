<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\Evidence;
use App\Models\User;
use App\Services\ReviewQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * The queue of the presidency: what is sent to its committee and what the coordinators send to the committee they
 * coordinate. The presidents review it as coordinators; the lecturer may too.
 */
class PresidencyReviewController extends ReviewQueueController
{
    protected function kind(): string
    {
        return ReviewQueue::PRESIDENCY;
    }

    protected function evidences(User $user): Builder
    {
        return $this->queue->forPresidency()->where('user_id', '!=', $user->id);
    }

    protected function committee(User $user): ?Committee
    {
        return $this->queue->presidency();
    }

    protected function reviews(User $user, Evidence $evidence): bool
    {
        return $this->queue->reviewsAsPresidency($user, $evidence);
    }

    /** Detail of an evidence of the queue; the author reads theirs in evidence.view. */
    public function view($id)
    {
        $evidence = Evidence::findOrFail($id);
        abort_unless($evidence->status !== 'DRAFT' && $this->queue->goesToPresidency($evidence) && $evidence->user_id != Auth::id(), 404);

        return view('evidence.view', ['evidence' => $evidence]);
    }
}
