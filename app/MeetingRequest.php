<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetingRequest extends Model
{
    protected $table = "meeting_request";

    protected $fillable = [
      'meeting_id','date','place'
    ];

    public function meeting()
    {
        return $this->hasOne('App\Meeting');
    }

    public function diary()
    {
        return $this->hasOne('App\Diary');
    }
}
