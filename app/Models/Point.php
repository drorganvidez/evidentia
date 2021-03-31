<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    public function meetingMinutes()
    {
        return $this->belongsTo('App\Models\MeetingMinutes');
    }

    public function agreements()
    {
        return $this->hasMany('App\Models\Agreement');
    }
}
