<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diary extends Model
{

    protected $table = "diaries";

    protected $fillable = [
        "meeting_request_id"
    ];

    public function MeetingRequest()
    {
        return $this->belongsTo('App\Models\MeetingRequest');
    }

    public function diary_points()
    {
        return $this->hasMany('App\Models\DiaryPoints');
    }
}
