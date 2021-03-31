<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingMinutes extends Model
{
    protected $table = "meeting_minutes";

    public function meeting()
    {
        return $this->belongsTo('App\Models\Meeting');
    }

    public function points()
    {
        return $this->hasMany('App\Models\Point');
    }
}
