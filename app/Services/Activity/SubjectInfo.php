<?php

namespace App\Services\Activity;

use App\Models\Attendee;
use App\Models\Bonus;
use App\Models\Certificate;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\DefaultList;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\MessageThread;
use App\Models\SignatureSheet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * What the log copies from a subject: its title, its committee and the root of its versions, plus the names of the
 * people and the cleaning of the details.
 */
final class SubjectInfo
{
    /** Keys that never reach the log, at any depth. */
    private const SECRET_KEYS = [
        'password', 'password_confirmation', 'current_password', 'token', '_token', 'eventbrite_token',
        'remember_token', 'credentials', 'secret',
    ];

    private const MAX_LENGTH = 2000;

    public static function title(Model $subject): string
    {
        return (string) match (true) {
            $subject instanceof Evidence => $subject->title,
            $subject instanceof MeetingRequest => $subject->title,
            $subject instanceof SignatureSheet => $subject->title,
            $subject instanceof MeetingMinutes => $subject->meeting?->title,
            $subject instanceof Meeting => $subject->title,
            $subject instanceof Bonus => $subject->reason,
            $subject instanceof DefaultList => $subject->name,
            $subject instanceof Committee => $subject->name,
            $subject instanceof User => self::personName($subject),
            $subject instanceof Event => $subject->name,
            $subject instanceof Attendee => implode(' · ', array_filter([
                (string) $subject->event?->name,
                $subject->user ? self::personName($subject->user) : '',
            ], fn (string $part): bool => $part !== '')),
            $subject instanceof Configuration => 'Configuración del curso',
            $subject instanceof MessageThread => $subject->subject,
            $subject instanceof Certificate => $subject->typeLabel().' · '.$subject->recipient_name,
            default => class_basename($subject).' '.$subject->getKey(),
        };
    }

    /** Committee the annotation belongs to; null for drafts, which never reach a committee. */
    public static function committeeId(Model $subject): ?int
    {
        $id = match (true) {
            $subject instanceof Evidence => $subject->status === 'DRAFT' ? null : $subject->committee_id,
            $subject instanceof MeetingRequest, $subject instanceof Meeting, $subject instanceof Bonus => $subject->committee_id,
            $subject instanceof MeetingMinutes => $subject->meeting?->committee_id,
            $subject instanceof SignatureSheet => $subject->meetingRequest?->committee_id ?? $subject->secretary?->committee_id,
            $subject instanceof DefaultList => $subject->secretary?->committee_id,
            $subject instanceof Committee => $subject->getKey(),
            default => null,
        };

        return $id === null ? null : (int) $id;
    }

    /** First version of an evidence, so that every version shares one history; the key itself for the rest. */
    public static function rootId(Model $subject): ?int
    {
        $id = $subject instanceof Evidence ? $subject->rootEvidence()->getKey() : $subject->getKey();

        return $id === null ? null : (int) $id;
    }

    /** «Apellidos, Nombre», or the name alone without surname. */
    public static function personName(User $user): string
    {
        $name = trim((string) $user->name);
        $surname = trim((string) $user->surname);

        return $surname === '' ? $name : $surname.', '.$name;
    }

    /** Drops passwords and tokens at any depth and cuts long texts. */
    public static function sanitize(array $details): array
    {
        $clean = [];
        foreach ($details as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), self::SECRET_KEYS, true)) {
                continue;
            }
            $clean[$key] = match (true) {
                is_array($value) => self::sanitize($value),
                is_string($value) => mb_substr($value, 0, self::MAX_LENGTH),
                default => $value,
            };
        }

        return $clean;
    }

    /** @return list<class-string<Model>> */
    public static function supported(): array
    {
        return [
            Evidence::class, MeetingRequest::class, SignatureSheet::class, MeetingMinutes::class, Meeting::class,
            Bonus::class, DefaultList::class, Committee::class, User::class, Event::class, Attendee::class,
            Configuration::class, MessageThread::class, Certificate::class,
        ];
    }
}
