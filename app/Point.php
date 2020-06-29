<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    public function meetingMinutes()
    {
        return $this->belongsTo('App\MeetingMinutes');
    }

    public function agreements()
    {
        return $this->hasMany('App\Agreement');
    }
}
