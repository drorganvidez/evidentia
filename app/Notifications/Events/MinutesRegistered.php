<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\FormatsNotices;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Minutes registered or edited, to the people they count as attendees (the secretary who writes them aside). */
class MinutesRegistered extends BaseNotification
{
    use FormatsNotices;

    public static function key(): string
    {
        return 'minutes.registered';
    }

    public static function label(): string
    {
        return 'Te incluyen en un acta';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Important;
    }

    public static function triggers(): array
    {
        return [ActivityAction::MinutesCreated, ActivityAction::MinutesEdited];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        $ids = $activity->action === ActivityAction::MinutesEdited->value
            ? array_map('intval', (array) ($activity->details['users_added'] ?? []))
            : DB::table('activity_log_targets')->where('activity_log_id', $activity->id)->pluck('user_id')->map(fn ($id): int => (int) $id)->all();

        $minutes = $activity->subject;
        $secretary = $minutes instanceof MeetingMinutes ? $minutes->secretary?->user_id : null;

        return $ids === [] ? collect() : User::query()
            ->whereKey($ids)
            ->when($secretary !== null, fn ($query) => $query->whereKeyNot($secretary))
            ->get();
    }

    public function title(User $notifiable): string
    {
        return 'Te han incluido en un acta';
    }

    public function text(User $notifiable): ?string
    {
        $meeting = $this->meeting();

        return implode(' · ', array_filter([
            $this->meetingTitle(),
            self::hours($meeting?->hours).' h',
            $this->meetingDate() ? self::day($this->meetingDate()) : null,
        ]));
    }

    public function url(User $notifiable): string
    {
        $id = $this->activity?->subject_id;

        return $id === null ? route('meeting.list') : self::link('activity.minutes', ['id' => $id], 'meeting.list');
    }

    public function tone(): string
    {
        return 'accepted';
    }

    public function mailSubject(User $notifiable): string
    {
        return 'Acta registrada · '.$this->meetingTitle();
    }

    public function mailLines(User $notifiable): array
    {
        $meeting = $this->meeting();
        $secretary = self::fullName($this->activity?->user, $this->activity?->user_name);
        $date = $this->meetingDate() ? ' del '.self::day($this->meetingDate()) : '';
        $committee = $meeting?->committee?->name ?? $this->activity?->committee_name;

        return [
            $secretary.' ha registrado el acta de «'.$this->meetingTitle().'»'.$date.' en el comité '.$committee.'.',
            'Se te computan '.self::hours($meeting?->hours).' h de reunión.',
        ];
    }

    public function mailAction(User $notifiable): string
    {
        return 'Ver el acta';
    }

    private function meeting(): ?Meeting
    {
        $minutes = $this->activity?->subject;

        return $minutes instanceof MeetingMinutes ? $minutes->meeting : null;
    }

    private function meetingDate(): ?CarbonImmutable
    {
        $at = $this->meeting()?->datetime;

        return blank($at) ? null : CarbonImmutable::parse($at);
    }

    private function meetingTitle(): string
    {
        return (string) ($this->meeting()?->title ?? $this->activity?->subject_title);
    }
}
