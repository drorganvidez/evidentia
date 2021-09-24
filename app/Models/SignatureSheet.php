<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureSheet extends Model
{
    use HasFactory;

    protected $fillable = [
      'title','random_identifier', 'meeting_request_id', 'secretary_id'
    ];

    public function users()
    {
        return $this->belongsToMany('App\Models\User')->withTimestamps()->orderByDesc('created_at');
    }

    public function meeting_request()
    {
        return $this->belongsTo('App\Models\MeetingRequest');
    }

    public function secretary()
    {
        return $this->belongsTo('App\Models\Secretary');
    }
}
