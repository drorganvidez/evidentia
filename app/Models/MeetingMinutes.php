<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingMinutes extends Model
{
    protected $table = "meeting_minutes";

    protected $fillable = [
        'meeting_id'
    ];

    public function meeting()
    {
        return $this->belongsTo('App\Models\Meeting');
    }
}
