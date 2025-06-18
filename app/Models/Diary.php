<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diary extends Model
{
    protected $table = 'diaries';

    protected $fillable = [
        'meeting_request_id',
    ];

    /**
     * Get the meeting request associated with this diary.
     */
    public function meetingRequest()
    {
        return $this->belongsTo(MeetingRequest::class);
    }

    /**
     * Get the diary points of this diary.
     */
    public function diaryPoints()
    {
        return $this->hasMany(DiaryPoint::class);
    }
}
