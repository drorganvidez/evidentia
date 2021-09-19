<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingRequest extends Model
{
    protected $table = "meeting_request";

    protected $fillable = [
      'meeting_id','datetime','place','type','modality'
    ];

    public function meeting()
    {
        return $this->hasOne('App\Models\Meeting');
    }

    public function diary()
    {
        return $this->hasOne('App\Models\Diary');
    }
}
