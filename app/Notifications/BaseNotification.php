<?php

namespace App\Notifications;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Notifications\NotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * A notice derived from an annotation of the activity log: always to the inbox, by mail depending on its level and
 * the preferences of the person. The catalogue finds the subclasses in app/Notifications/Events and Reminders.
 */
abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** Channels via() returned for this person; notification.sent records them. */
    public array $channels = [];

    /** Not readonly: the queue restores it after serializing the notice. */
    public ?ActivityLog $activity = null;

    public function __construct(?ActivityLog $activity = null)
    {
        $this->activity = $activity;
        $this->afterCommit();
    }

    /** Stable key: the type of the inbox row and the key of the preferences. */
    abstract public static function key(): string;

    /** Name in the preferences screen. */
    abstract public static function label(): string;

    abstract public static function level(): NotificationLevel;

    /** @return list<string> roles it can reach ([] = every role); filters the preferences screen */
    public static function audience(): array
    {
        return [];
    }

    /** @return list<ActivityAction> verbs that send it */
    public static function triggers(): array
    {
        return [];
    }

    /** @return Collection<int, User> */
    public static function recipients(ActivityLog $activity): Collection
    {
        return collect();
    }

    public static function inPreferences(): bool
    {
        return true;
    }

    /** Title in the inbox. */
    abstract public function title(User $notifiable): string;

    /** Text in the inbox. */
    abstract public function text(User $notifiable): ?string;

    /** Absolute link (route()); the inbox keeps its relative path. */
    abstract public function url(User $notifiable): string;

    /** accepted | rejected | pending | notice | draft */
    abstract public function tone(): string;

    abstract public function mailSubject(User $notifiable): string;

    /** @return list<string> */
    abstract public function mailLines(User $notifiable): array;

    /** Quoted text (a reason, a message). */
    public function mailPanel(User $notifiable): ?string
    {
        return null;
    }

    /** @return list<string> list under the lines (an agenda, some titles) */
    public function mailItems(User $notifiable): array
    {
        return [];
    }

    /** Label of the button. */
    abstract public function mailAction(User $notifiable): string;

    public function subject(): ?Model
    {
        return $this->activity?->subject;
    }

    public function via(object $notifiable): array
    {
        return $this->channels = app(NotificationPreferences::class)->channelsFor($notifiable, $this);
    }

    public function databaseType(object $notifiable): string
    {
        return static::key();
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title($notifiable),
            'text' => $this->text($notifiable),
            'tone' => $this->tone(),
            'url' => self::relative($this->url($notifiable)),
            'level' => static::level()->value,
            'key' => static::key(),
            'subject_type' => $this->activity?->subject_type,
            'subject_id' => $this->activity?->subject_id,
            'subject_root_id' => $this->activity?->subject_root_id,
            'activity_id' => $this->activity?->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // the extra data goes with markdown(): with() would add a line to the mail
        return (new MailMessage)
            ->subject($this->mailSubject($notifiable))
            ->greeting('Hola, '.$notifiable->name)
            ->lines($this->mailLines($notifiable))
            ->markdown('notifications::email', ['panel' => $this->mailPanel($notifiable), 'items' => $this->mailItems($notifiable)])
            ->action($this->mailAction($notifiable), $this->url($notifiable))
            ->salutation('Evidentia');
    }

    /** Path and query of a link of the app, so that the inbox never depends on the host. */
    private static function relative(string $url): string
    {
        $parts = parse_url($url);

        return ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
    }
}
