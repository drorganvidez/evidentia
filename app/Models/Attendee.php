<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Event;
use App\Models\User;

class Attendee extends Model
{
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
}
