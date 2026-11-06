<?php

namespace App\Services\Messaging;

use App\Models\Evidence;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The person in charge of each list of a student, so that whatever goes wrong they write to who can fix it: the
 * coordination for the evidences (the committee of the latest accepted evidence first), the secretary for the
 * meetings (the committee of the latest meeting attended or sheet signed) and the register coordination for the
 * attendances. Each link opens the message form with the addressee and the subject already chosen; the form still
 * lets them pick any other committee.
 */
final class QuickContact
{
    public static function coordination(User $user): string
    {
        return self::href(self::evidenceCommittee($user), 'COORDINATOR', 'Sobre mis evidencias');
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
            ?? self::evidenceCommittee($user);

        return self::href($committee === null ? null : (int) $committee, 'SECRETARY', 'Sobre mis reuniones');
    }

    public static function register(): string
    {
        return route('messages.create', ['to' => 'REGISTER_COORDINATOR', 'subject' => 'Sobre mis asistencias']);
    }

    /** The committee of the latest accepted evidence of the person, otherwise of the latest one they sent. */
    private static function evidenceCommittee(User $user): ?int
    {
        $sent = fn () => Evidence::where('user_id', $user->id)->where('last', true)->whereNotNull('committee_id')
            ->orderByDesc('updated_at')->orderByDesc('id');
        $committee = $sent()->where('status', 'ACCEPTED')->value('committee_id')
            ?? $sent()->whereIn('status', ['PENDING', 'REJECTED'])->value('committee_id');

        return $committee === null ? null : (int) $committee;
    }

    private static function href(?int $committee, string $role, string $subject): string
    {
        return route('messages.create', array_filter(['to' => $committee === null ? null : $role.':'.$committee, 'subject' => $subject]));
    }
}
