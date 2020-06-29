<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetingMinutes extends Model
{
    protected $table = "meeting_minutes";

    public function meeting()
    {
        return $this->belongsTo('App\Meeting');
    }

    public function points()
    {
        return $this->hasMany('App\Point');
    }
}
