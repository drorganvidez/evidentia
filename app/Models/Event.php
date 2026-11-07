<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';

    /** The statuses of Eventbrite along the life of an event, the order of the lists */
    public const STATUS_ORDER = ['draft', 'live', 'started', 'ended', 'completed', 'canceled'];

    protected $fillable = [
        'name',
        'description',
        'id_eventbrite',
        'start_datetime',
        'end_datetime',
        'capacity',
        'status',
        'url',
        'hours',
        'hidden',
        'event_group',
    ];

    /**
     * Hours that count from a set of attended events: an event without group counts whole, and of the events that share
     * a group (held in parallel or the same talk repeated) only the longest one counts.
     *
     * @param  iterable<Event>  $events
     */
    public static function countedHours(iterable $events): float
    {
        return (float) collect($events)->filter()
            ->groupBy(fn (Event $event) => $event->event_group ?? '#'.$event->id)
            ->sum(fn ($group) => (float) $group->max('hours'));
    }

    /**
     * Get the attendees for the event.
     */
    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }
}
