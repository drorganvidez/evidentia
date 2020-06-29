<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Diary extends Model
{
    public function MeetingRequest()
    {
        return $this->belongsTo('App\MeetingRequest');
    }

    public function DiaryPoints()
    {
        return $this->hasMany('App\DiaryPoints');
    }
}
