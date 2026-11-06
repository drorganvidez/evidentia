<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\Evidence;
use App\Models\ReasonRejection;
use Illuminate\Support\Facades\DB;

/**
 * Acceptance and rejection of an evidence, shared by the coordinator and the management. The validation deadline of
 * the verbs flags a review made after it closed.
 */
final class EvidenceReview
{
    public function accept(Evidence $evidence, ?string $actingRole = null): Evidence
    {
        return DB::transaction(function () use ($evidence, $actingRole): Evidence {
            $before = $evidence->status;
            $evidence->status = 'ACCEPTED';
            $evidence->save();

            Activity::record(
                ActivityAction::EvidenceAccepted,
                $evidence,
                $evidence->user,
                details: ['before' => $before, 'after' => 'ACCEPTED'],
                actingRole: $actingRole,
            );

            return $evidence;
        });
    }

    public function reject(Evidence $evidence, string $reason, ?string $actingRole = null): Evidence
    {
        return DB::transaction(function () use ($evidence, $reason, $actingRole): Evidence {
            $before = $evidence->status;
            $evidence->status = 'REJECTED';
            $evidence->save();

            $rejection = ReasonRejection::create(['reason' => $reason, 'evidence_id' => $evidence->id]);

            Activity::record(
                ActivityAction::EvidenceRejected,
                $evidence,
                $evidence->user,
                details: ['before' => $before, 'after' => 'REJECTED', 'reason' => $reason, 'reason_rejection_id' => $rejection->id],
                actingRole: $actingRole,
            );

            return $evidence;
        });
    }
}
