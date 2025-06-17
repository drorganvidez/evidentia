<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Committee;
use App\Models\MeetingMinutes;

class Meeting extends Model
{

    protected $table = 'meetings';

    protected $fillable = [
        'title',
        'datetime',
        'place',
        'type',
        'modality',
        'hours',
    ];

    /**
     * The users attending the meeting.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * The committee that organizes the meeting.
     */
    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * The minutes recorded for this meeting.
     */
    public function meetingMinutes()
    {
        return $this->hasOne(MeetingMinutes::class);
    }
}
