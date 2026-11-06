<?php

namespace Database\Seeders\E2e;

use App\Enums\ActivityAction;
use App\Enums\ActivityFlag;
use App\Facades\Activity;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\MeetingRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Annotations of the end-to-end seed: the review of the evidences of Logística (with one blocked self review), a
 * meeting request with its sheet in one action, and a change in another committee the coordinator must not see.
 * Each action gets its own request id, as if it came from its own request.
 */
class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $who = fn (string $username): User => User::where('username', $username)->sole();
        $evidence = fn (string $title): Evidence => Evidence::where('title', $title)->sole();
        $student = $who('estudiante');
        $coordinator = $who('coordinador');

        foreach (['Montaje del photocall', 'Cartelería del vestíbulo', 'Recogida de material'] as $title) {
            Activity::startRequest();
            Activity::record(ActivityAction::EvidencePublished, $evidence($title), details: ['version' => 1, 'hours' => $evidence($title)->hours],
                deadline: false, actingRole: 'STUDENT', actor: $student);
        }

        Activity::startRequest();
        Activity::record(ActivityAction::EvidenceAccepted, $evidence('Cartelería del vestíbulo'), $student,
            details: ['before' => 'PENDING', 'after' => 'ACCEPTED'], deadline: false, actingRole: 'COORDINATOR', actor: $coordinator);

        Activity::startRequest();
        $rejected = $evidence('Recogida de material');
        Activity::record(ActivityAction::EvidenceRejected, $rejected, $student,
            details: ['before' => 'PENDING', 'after' => 'REJECTED', 'reason' => $rejected->reasonRejection?->reason],
            deadline: false, actingRole: 'COORDINATOR', actor: $coordinator);

        Activity::startRequest();
        Activity::record(ActivityAction::SelfReviewBlocked, $evidence('Atención en el registro'), details: ['attempted' => 'evidence.accepted'],
            flags: [ActivityFlag::SelfReview], deadline: false, actingRole: 'COORDINATOR', actor: $coordinator);

        Activity::startRequest();
        $request = MeetingRequest::with('signatureSheet')->firstOrFail();
        $secretary = $who('secretario');
        Activity::record(ActivityAction::RequestCreated, $request, details: ['datetime' => (string) $request->datetime, 'place' => $request->place],
            actingRole: 'SECRETARY', actor: $secretary);
        Activity::record(ActivityAction::SheetCreated, $request->signatureSheet, actingRole: 'SECRETARY', actor: $secretary);

        Activity::startRequest();
        $communication = Committee::where('name', 'Comunicación')->sole();
        Activity::record(ActivityAction::CommitteeEdited, $communication,
            details: ['before' => ['name' => 'Difusión'], 'after' => ['name' => 'Comunicación']], actingRole: 'LECTURE', actor: $who('profesor'));

        Activity::startRequest();
    }
}
