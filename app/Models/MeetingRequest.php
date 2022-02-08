<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MeetingRequest extends Model
{
    protected $table = "meeting_requests";

    protected $fillable = [
      'meeting_id','datetime','place','type','modality', 'comittee_id', 'secretary_id', 'title'
    ];

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

    public static function next_meeting_requests() {
        return MeetingRequest::where('datetime', '>=', Carbon::now())->orderBy('datetime', 'asc')->get()->take(7);
    }

}
