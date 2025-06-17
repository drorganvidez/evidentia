<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Attendee;

class Event extends Model
{

    protected $table = 'events';

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
    ];

    /**
     * Get the attendees for the event.
     */
    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }
}
