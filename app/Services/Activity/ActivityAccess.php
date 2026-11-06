<?php

namespace App\Services\Activity;

use App\Enums\Deadline;
use App\Models\Evidence;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\User;
use App\Services\Deadlines;
use App\Services\ReviewQueue;
use App\Support\Roles;
use Illuminate\Support\Facades\Route;

/**
 * Who sees what in the activity log and in the histories, and who may move or review an evidence from them.
 * The management sees everything; coordinators and secretaries their committee; drafts only their author.
 */
final class ActivityAccess
{
    public function __construct(private Deadlines $deadlines, private ReviewQueue $queue) {}

    public function isManager(User $user): bool
    {
        return Roles::isManager($user);
    }

    /**
     * Committees the log is narrowed to: null for the management (no limit), the committees of a coordinator or a
     * secretary, and none for the rest.
     *
     * @return list<int>|null
     */
    public function committeeScope(User $user): ?array
    {
        return $this->isManager($user) ? null : Roles::committeeIds($user);
    }

    public function canViewLog(User $user): bool
    {
        return $this->committeeScope($user) !== [];
    }

    public function canExport(User $user): bool
    {
        return $this->isManager($user);
    }

    /** The author always; the committee and the management once it is no longer a draft. */
    public function canViewEvidence(User $user, Evidence $head): bool
    {
        if ($this->isAuthor($user, $head)) {
            return true;
        }

        return $head->status !== 'DRAFT' && ($this->isManager($user) || $this->inCommittee($user, $head->committee_id));
    }

    public function seesFullEvidenceHistory(User $user, Evidence $head): bool
    {
        return $this->isAuthor($user, $head) || $this->isManager($user);
    }

    public function canViewMinutesHistory(User $user, MeetingMinutes $minutes): bool
    {
        return $this->isManager($user)
            || $this->isSecretary($user, $minutes->secretary_id)
            || $this->inCommittee($user, $minutes->meeting?->committee_id)
            || ($minutes->meeting?->users()->whereKey($user->getKey())->exists() ?? false);
    }

    public function canViewRequestHistory(User $user, MeetingRequest $request): bool
    {
        return $this->isManager($user)
            || $this->isSecretary($user, $request->secretary_id)
            || $this->inCommittee($user, $request->committee_id);
    }

    /** Names of who signed a sheet: only its secretary and the management. */
    public function seesSignatures(User $user, MeetingRequest $request): bool
    {
        return $this->isManager($user) || $this->isSecretary($user, $request->secretary_id);
    }

    public function canMoveAsStudent(User $user, Evidence $evidence): bool
    {
        return $this->isAuthor($user, $evidence)
            && $evidence->last
            && in_array($evidence->status, ['DRAFT', 'REJECTED', 'PENDING'], true)
            && $this->deadlines->isOpen(Deadline::UploadEvidences);
    }

    /** Only an evidence the coordinator reviews (App\Services\ReviewQueue), while it waits and validation is open. */
    public function canMoveAsCoordinator(User $user, Evidence $evidence): bool
    {
        return $evidence->status === 'PENDING'
            && $this->queue->reviewsAsCoordinator($user, $evidence)
            && $this->deadlines->isOpen(Deadline::ValidateEvidences);
    }

    public function canMoveAsManager(User $user, Evidence $evidence): bool
    {
        return $this->isManager($user) && $evidence->last && $evidence->status !== 'DRAFT';
    }

    public function canReviewAsManager(User $user, Evidence $evidence): bool
    {
        return $this->canMoveAsManager($user, $evidence) && ! $this->isAuthor($user, $evidence);
    }

    /** The old detail page of the evidence this person can open, if any. */
    public function evidenceDetailHref(User $viewer, Evidence $head): ?string
    {
        return match (true) {
            $this->isAuthor($viewer, $head) => route('evidence.view', ['id' => $head->id]),
            $head->status === 'DRAFT' => null,
            $this->isCoordinatorOf($viewer, $head->committee_id) => route('coordinator.evidence.view', ['id' => $head->id]),
            $viewer->hasRole('PRESIDENT') && $head->last && $this->queue->goesToPresidency($head) => route('president.review.view', ['id' => $head->id]),
            $this->isManager($viewer) => route('profiles.view.evidence', ['id_user' => $head->user_id, 'id_evidence' => $head->id]),
            default => null,
        };
    }

    /** The profile page of a person, only for the management and only if the person still exists. */
    public function profileHref(User $viewer, ?int $userId): ?string
    {
        if ($userId === null || ! $this->isManager($viewer) || ! Route::has('profiles.view') || ! User::whereKey($userId)->exists()) {
            return null;
        }

        return route('profiles.view', ['id' => $userId]);
    }

    private function isAuthor(User $user, Evidence $evidence): bool
    {
        return (int) $evidence->user_id === (int) $user->getKey();
    }

    private function inCommittee(User $user, ?int $committeeId): bool
    {
        return $committeeId !== null && in_array((int) $committeeId, Roles::committeeIds($user), true);
    }

    private function isCoordinatorOf(User $user, ?int $committeeId): bool
    {
        return $committeeId !== null
            && $user->hasRole('COORDINATOR')
            && (int) $user->coordinator?->committee_id === (int) $committeeId;
    }

    private function isSecretary(User $user, ?int $secretaryId): bool
    {
        return $secretaryId !== null && (int) $user->secretary?->getKey() === (int) $secretaryId;
    }
}
