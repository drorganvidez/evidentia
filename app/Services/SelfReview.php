<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Enums\ActivityFlag;
use App\Facades\Activity;
use App\Models\Attendee;
use App\Models\Evidence;
use App\Models\SignatureSheet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Nobody reviews their own work: the attempt is blocked and stays in the log.
 */
final class SelfReview
{
    /** Whether the subject (or the person id) is the actor's own work. */
    public static function violates(User $actor, Model|int|null $subject): bool
    {
        return match (true) {
            $subject === null => false,
            is_int($subject) => $subject === (int) $actor->getKey(),
            $subject instanceof Evidence, $subject instanceof Attendee => (int) $subject->user_id === (int) $actor->getKey(),
            $subject instanceof SignatureSheet => $actor->secretary !== null && (int) $actor->secretary->getKey() === (int) $subject->secretary_id,
            default => false,
        };
    }

    /**
     * Annotates self_review.blocked and goes back with the error. Called before any transaction opens, so the
     * annotation survives.
     */
    public static function deny(Model $subject, ActivityAction $attempted, ?User $target, string $message): never
    {
        Activity::record(
            ActivityAction::SelfReviewBlocked,
            $subject,
            $target,
            details: ['attempted' => $attempted->value],
            flags: [ActivityFlag::SelfReview],
            deadline: false,
        );

        throw new HttpResponseException(back()->with('error', $message));
    }
}
