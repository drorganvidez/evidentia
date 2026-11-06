<?php

namespace Database\Seeders\E2e;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\Evidence;
use App\Models\MeetingMinutes;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * Notices of the end-to-end seed, sent by the real classes from annotations of the seed: the coordinators and the
 * secretary get the two pending evidences of Logística, the president the two of the queue of the presidency, the
 * student her rejection, her acceptance (already read) and the minutes that include her, and the coordinators and the
 * president the weekly summary of their queues. Each action has its own request id.
 */
class NotificationsSeeder extends Seeder
{
    public function run(): void
    {
        $person = fn (string $username): User => User::where('username', $username)->sole();
        $evidence = fn (string $title): Evidence => Evidence::where('title', $title)->sole();
        [$student, $classmate, $coordinator, $cocoordinator, $secretary] = array_map($person, ['estudiante', 'companero', 'coordinador', 'coordinadora', 'secretario']);

        foreach ([[$student, 'Montaje del photocall'], [$classmate, 'Atención en el registro'], [$cocoordinator, 'Inventario del almacén'],
            [$secretary, 'Acreditación de ponentes']] as [$author, $title]) {
            Activity::startRequest();
            $sent = $evidence($title);
            Activity::record(ActivityAction::EvidencePublished, $sent, details: [
                'status' => 'PENDING', 'hours' => (float) $sent->hours, 'committee_id' => (int) $sent->committee_id, 'version' => 1,
            ], actor: $author, actingRole: 'STUDENT');
        }

        Activity::startRequest();
        Activity::record(ActivityAction::EvidenceAccepted, $evidence('Cartelería del vestíbulo'), $student,
            details: ['before' => 'PENDING', 'after' => 'ACCEPTED'], actor: $coordinator, actingRole: 'COORDINATOR');
        $student->unreadNotifications()->update(['read_at' => now()]);

        Activity::startRequest();
        $rejected = $evidence('Recogida de material');
        Activity::record(ActivityAction::EvidenceRejected, $rejected, $student, details: [
            'before' => 'PENDING', 'after' => 'REJECTED', 'reason' => $rejected->reasonRejection?->reason ?? 'Faltan las fotos del montaje terminado.',
        ], actor: $coordinator, actingRole: 'COORDINATOR');

        Activity::startRequest();
        $minutes = MeetingMinutes::query()->firstOrFail();
        Activity::record(ActivityAction::MinutesCreated, $minutes, details: [
            'meeting_id' => $minutes->meeting_id, 'users' => 2, 'self_included' => true,
        ], targets: [$student, $secretary], actor: $secretary, actingRole: 'SECRETARY');

        Artisan::call('notifications:review-queue', ['--kind' => 'summary']);
    }
}
