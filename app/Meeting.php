<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $table = "meeting";

    protected $fillable = [
      'id','title','datetime','place','type','hours'
    ];

    public function users()
    {
        return $this->hasMany('App\User');
    }

    public function comittee()
    {
        return $this->belongsTo('App\Comittee');
    }

    public function meetingRequest()
    {
        return $this->belongsTo('App\MeetingRequest');
    }

    public function meetingMinutes()
    {
        return $this->hasMany('App\MeetingMinutes');
    }
}
