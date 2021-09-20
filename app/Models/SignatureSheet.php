<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureSheet extends Model
{
    use HasFactory;

    protected $fillable = [
      'random_identifier',
      'meeting_request_id',
      'meeting_minutes_id'
    ];

    public function users()
    {
        return $this->hasMany('App\Models\User');
    }

    public function meeting_request()
    {
        return $this->belongsTo('App\Models\MeetingRequest');
    }

    public function meeting_minutes()
    {
        return $this->belongsTo('App\Models\MeetingMinutes');
    }
}
