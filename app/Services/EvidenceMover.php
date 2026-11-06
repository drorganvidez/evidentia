<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Enums\Deadline;
use App\Facades\Activity;
use App\Models\Committee;
use App\Models\Evidence;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Moves an evidence to another committee by changing the committee of its last version: the id, the proofs, the
 * description, the stamp, the rejection reasons and the earlier versions stay as they were.
 */
final class EvidenceMover
{
    /** Drafts and rejected ones depend on the upload deadline; pending and accepted ones on the validation one. */
    public static function governingDeadline(Evidence $evidence): Deadline
    {
        return in_array($evidence->status, ['PENDING', 'ACCEPTED'], true)
            ? Deadline::ValidateEvidences
            : Deadline::UploadEvidences;
    }

    /**
     * Only the management passes $checkDeadline: the student and the coordinator are already behind the middleware
     * of their deadline, and checking again could flag them when validation closes the same day before upload.
     */
    public function move(Evidence $evidence, Committee $to, ?string $actingRole = null, bool $checkDeadline = false): Evidence
    {
        if (! $evidence->last) {
            throw new LogicException('Only the last version of an evidence can be moved.');
        }
        if ((int) $evidence->committee_id === (int) $to->getKey()) {
            throw new DomainException('Elige un comité distinto del actual.');
        }

        $from = $evidence->committee;
        $before = ['committee_id' => $evidence->committee_id === null ? null : (int) $evidence->committee_id, 'committee_name' => $from?->name];
        $after = ['committee_id' => (int) $to->getKey(), 'committee_name' => $to->name];
        $author = (int) $evidence->user_id === (int) Auth::id() ? null : $evidence->user;

        return DB::transaction(function () use ($evidence, $to, $before, $after, $author, $actingRole, $checkDeadline): Evidence {
            $evidence->committee()->associate($to);
            $evidence->save();

            Activity::record(
                ActivityAction::EvidenceMoved,
                $evidence,
                $author,
                details: ['before' => $before, 'after' => $after, 'status' => $evidence->status],
                deadline: $checkDeadline ? self::governingDeadline($evidence) : false,
                actingRole: $actingRole,
                // el comité que pierde la evidencia; un borrador nunca llegó a ninguno
                committeeId: $evidence->status === 'DRAFT' || $before['committee_id'] === null ? false : $before['committee_id'],
            );

            return $evidence;
        });
    }
}
