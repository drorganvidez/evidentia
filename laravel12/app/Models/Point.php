<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MeetingMinutes;
use App\Models\Agreement;

class Point extends Model
{
    protected $table = 'points';

    protected $fillable = [
        'meeting_minutes_id',
        'title',
        'duration',
        'description',
    ];

    /**
     * Get the meeting minutes this point belongs to.
     */
    public function meetingMinutes()
    {
        return $this->belongsTo(MeetingMinutes::class);
    }

    /**
     * Get the agreements associated with this point.
     */
    public function agreements()
    {
        return $this->hasMany(Agreement::class);
    }
}
