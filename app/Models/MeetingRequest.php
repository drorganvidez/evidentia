<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingRequest extends Model
{
    protected $table = "meeting_request";

    protected $fillable = [
      'meeting_id','datetime','place','type','modality', 'comittee_id', 'secretary_id', 'title'
    ];

    public function meeting()
    {
        return $this->hasOne('App\Models\Meeting');
    }

    public function diary()
    {
        return $this->hasOne('App\Models\Diary');
    }

    public function comittee()
    {
        return $this->belongsTo('App\Models\Comittee');
    }

    public function secretary()
    {
        return $this->belongsTo('App\Models\Secretary');
    }

    public function signature_sheet()
    {
        return $this->hasOne('App\Models\SignatureSheet');
    }

}
