<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\MeetingRequest;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\FormatsNotices;
use App\Notifications\Recipients;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * A meeting request, to the coordinators and secretaries of its committee and to whoever has an evidence sent to it:
 * there is no list of people called, so only to the inbox unless someone asks for the mail.
 */
class MeetingConvened extends BaseNotification
{
    use FormatsNotices;

    public static function key(): string
    {
        return 'meeting.convened';
    }

    public static function label(): string
    {
        return 'Convocatoria de reunión';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Informative;
    }

    public static function triggers(): array
    {
        return [ActivityAction::RequestCreated];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        $request = $activity->subject;
        if (! $request instanceof MeetingRequest || $request->committee_id === null) {
            return collect();
        }

        $committee = (int) $request->committee_id;
        $authors = User::query()
            ->whereHas('evidences', fn (Builder $evidences) => $evidences
                ->where('committee_id', $committee)
                ->where('last', true)
                ->where('status', '!=', 'DRAFT'))
            ->get();

        return Recipients::coordinators($committee)
            ->concat(Recipients::secretaries($committee))
            ->concat($authors)
            ->unique(fn (User $user) => $user->getKey())
            ->values();
    }

    public function title(User $notifiable): string
    {
        return 'Convocatoria de '.$this->committeeName();
    }

    public function text(User $notifiable): ?string
    {
        $at = $this->at();

        return implode(' · ', array_filter([
            $this->requestTitle(),
            $at ? self::day($at).' '.self::clock($at) : null,
            $this->request()?->place,
        ]));
    }

    public function url(User $notifiable): string
    {
        $id = $this->activity?->subject_id;

        return $id === null ? route('home') : self::link('activity.request', ['id' => $id], 'download.request', ['id' => $id]);
    }

    public function tone(): string
    {
        return 'pending';
    }

    public function mailSubject(User $notifiable): string
    {
        $at = $this->at();

        return 'Convocatoria · '.$this->requestTitle().($at ? ' · '.$at->format('d/m').' '.self::clock($at) : '');
    }

    public function mailLines(User $notifiable): array
    {
        $at = $this->at();
        $when = $at ? ' el '.self::day($at).' a las '.self::clock($at) : '';
        $where = filled($this->request()?->place) ? ' en '.$this->request()->place : '';
        $secretary = self::fullName($this->activity?->user, $this->activity?->user_name);

        return [$secretary.' convoca una reunión del comité '.$this->committeeName().$when.$where.'.'];
    }

    public function mailItems(User $notifiable): array
    {
        return $this->request()?->diary?->diaryPoints->pluck('point')->map(fn ($point): string => (string) $point)->values()->all() ?? [];
    }

    public function mailAction(User $notifiable): string
    {
        return 'Ver la convocatoria';
    }

    private function request(): ?MeetingRequest
    {
        $subject = $this->activity?->subject;

        return $subject instanceof MeetingRequest ? $subject : null;
    }

    private function requestTitle(): string
    {
        return (string) ($this->request()?->title ?? $this->activity?->subject_title);
    }

    private function committeeName(): string
    {
        return (string) ($this->request()?->committee?->name ?? $this->activity?->committee_name);
    }

    private function at(): ?CarbonImmutable
    {
        $at = $this->request()?->datetime;

        return blank($at) ? null : CarbonImmutable::parse($at);
    }
}
