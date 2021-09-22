<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $table = "points";

    protected $fillable = [
        'meeting_minutes_id',
        'title',
        'duration',
        'description'
    ];

    public function meeting_minutes()
    {
        return $this->belongsTo('App\Models\MeetingMinutes');
    }

    public function agreements()
    {
        return $this->hasMany('App\Models\Agreement');
    }
}
