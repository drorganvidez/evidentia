<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diary extends Model
{
    public function MeetingRequest()
    {
        return $this->belongsTo('App\Models\MeetingRequest');
    }

    public function DiaryPoints()
    {
        return $this->hasMany('App\Models\DiaryPoints');
    }
}
