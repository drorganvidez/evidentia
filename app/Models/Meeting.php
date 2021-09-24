<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $table = "meeting";

    protected $fillable = [
      'id','title','datetime','place','type','modality','hours'
    ];

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function comittee()
    {
        return $this->belongsTo('App\Models\Comittee');
    }

    public function meeting_minutes()
    {
        return $this->hasOne('App\Models\MeetingMinutes');
    }
}
