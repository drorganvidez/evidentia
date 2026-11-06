<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckRoles;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\User;
use App\Services\ReviewQueue;
use Illuminate\Database\Eloquent\Builder;

/**
 * The queue of the coordination of a committee: what is sent to it, except what one of its coordinators wrote, which
 * goes to the presidency.
 */
class EvidenceCoordinatorController extends ReviewQueueController
{
    public function __construct(ReviewQueue $queue)
    {
        parent::__construct($queue);
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':COORDINATOR');
    }

    protected function kind(): string
    {
        return ReviewQueue::COORDINATION;
    }

    protected function evidences(User $user): Builder
    {
        abort_if($user->coordinator?->committee === null, 404);

        return $this->queue->forCommittee($user->coordinator->committee_id)->where('user_id', '!=', $user->id);
    }

    protected function committee(User $user): ?Committee
    {
        return $user->coordinator->committee;
    }

    protected function reviews(User $user, Evidence $evidence): bool
    {
        return $this->queue->reviewsAsCoordinator($user, $evidence);
    }
}
