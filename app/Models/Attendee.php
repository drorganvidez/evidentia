<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendee extends Model
{
    /** The only status whose event counts towards the hours of the person (User::attendees_hours) */
    public const ATTENDED = 'Checked In';

    /** The statuses in the order of the lists: pending, attended, not attended, canceled */
    public const STATUS_ORDER = ['Attending', 'Guests Attending', 'Checked In', 'Guests Attended', 'Not Attending', 'Not Attending (Refunded/Canceled)'];

    protected $table = 'attendees';

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
    ];

    /**
     * Get the event this attendee belongs to.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who is attending the event.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Hours of the event this attendance counts, before the cap of the configuration on the total of a person. */
    public function computedHours(): float
    {
        return $this->status === self::ATTENDED ? (float) ($this->event?->hours ?? 0) : 0.0;
    }
}
