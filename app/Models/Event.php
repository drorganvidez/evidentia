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
    ];

    /**
     * Get the attendees for the event.
     */
    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }
}
