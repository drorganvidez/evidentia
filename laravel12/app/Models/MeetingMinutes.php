<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Meeting;
use App\Models\Point;
use App\Models\Secretary;

class MeetingMinutes extends Model
{
    protected $table = 'meeting_minutes';

    protected $fillable = [
        'meeting_id',
        'secretary_id',
    ];

    /**
     * Get the meeting that this minutes record belongs to.
     */
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * Get the points discussed in this meeting minutes.
     */
    public function points()
    {
        return $this->hasMany(Point::class);
    }

    /**
     * Get the secretary who wrote the minutes.
     */
    public function secretary()
    {
        return $this->belongsTo(Secretary::class);
    }
}
