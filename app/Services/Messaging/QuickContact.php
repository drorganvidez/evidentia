<?php

namespace App\Services\Messaging;

use App\Models\Evidence;
use App\Models\User;
use App\Services\ReviewQueue;
use Illuminate\Support\Facades\DB;

/**
 * The person in charge of each list of a student, so that whatever goes wrong they write to who can fix it: the
 * coordination for the evidences (the committee of the latest accepted evidence first, or the presidency when it reviews
 * it), the secretary for the
 * meetings (the committee of the latest meeting attended or sheet signed) and the register coordination for the
 * attendances. Each link opens the message form with the addressee and the subject already chosen; the form still
 * lets them pick any other committee.
 */
final class QuickContact
{
    public static function coordination(User $user): string
    {
        $evidence = self::latestEvidence($user);
        // the presidency reviews what is sent to its committee and what a coordinator writes
        if ($evidence !== null && app(ReviewQueue::class)->goesToPresidency($evidence)) {
            return route('messages.create', ['to' => 'PRESIDENT', 'subject' => 'Sobre mis evidencias']);
        }

        return self::href($evidence?->committee_id === null ? null : (int) $evidence->committee_id, 'COORDINATOR', 'Sobre mis evidencias');
    }

    /** The label of the way to coordination(): the presidency when it reviews the latest evidence. */
    public static function coordinationLabel(User $user): string
    {
        $evidence = self::latestEvidence($user);

        return $evidence !== null && app(ReviewQueue::class)->goesToPresidency($evidence) ? 'Escribir a presidencia' : 'Escribir a la coordinación';
    }

    public static function secretary(User $user): string
    {
        $committee = DB::table('meeting_user')->join('meetings', 'meetings.id', '=', 'meeting_user.meeting_id')
            ->where('meeting_user.user_id', $user->id)->whereNotNull('meetings.committee_id')
            ->orderByDesc('meetings.datetime')->value('meetings.committee_id')
            ?? DB::table('signature_sheet_user')
                ->join('signature_sheets', 'signature_sheets.id', '=', 'signature_sheet_user.signature_sheet_id')
                ->join('meeting_requests', 'meeting_requests.id', '=', 'signature_sheets.meeting_request_id')
                ->where('signature_sheet_user.user_id', $user->id)->whereNotNull('meeting_requests.committee_id')
                ->orderByDesc('meeting_requests.datetime')->value('meeting_requests.committee_id')
            ?? self::latestEvidence($user)?->committee_id;

        return self::href($committee === null ? null : (int) $committee, 'SECRETARY', 'Sobre mis reuniones');
    }

    public static function register(): string
    {
        return route('messages.create', ['to' => 'REGISTER_COORDINATOR', 'subject' => 'Sobre mis asistencias']);
    }

    /** The latest accepted evidence of the person with a committee, otherwise the latest one they sent. */
    private static function latestEvidence(User $user): ?Evidence
    {
        $sent = fn () => Evidence::where('user_id', $user->id)->where('last', true)->whereNotNull('committee_id')
            ->orderByDesc('updated_at')->orderByDesc('id');

        return $sent()->where('status', 'ACCEPTED')->first() ?? $sent()->whereIn('status', ['PENDING', 'REJECTED'])->first();
    }

    private static function href(?int $committee, string $role, string $subject): string
    {
        return route('messages.create', array_filter(['to' => $committee === null ? null : $role.':'.$committee, 'subject' => $subject]));
    }
}
