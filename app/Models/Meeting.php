<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $table = "meeting";

    protected $fillable = [
      'id','title','datetime','place','type','hours'
    ];

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function comittee()
    {
        return $this->belongsTo('App\Models\Comittee');
    }

    public function meetingRequest()
    {
        return $this->belongsTo('App\Models\MeetingRequest');
    }

    public function meetingMinutes()
    {
        return $this->hasMany('App\Models\MeetingMinutes');
    }
}
