<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secretary extends Model
{
    protected $table = "secretaries";

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function default_lists()
    {
        return $this->hasMany(DefaultList::class)->orderByDesc('created_at');
    }

    public function meeting_requests()
    {
        return $this->hasMany(MeetingRequest::class)->orderByDesc('created_at');
    }

    public function signature_sheets()
    {
        return $this->hasMany(SignatureSheet::class)->orderByDesc('created_at');
    }

    public function meeting_minutes()
    {
        return $this->hasMany(MeetingMinutes::class)->orderByDesc('updated_at');
    }
}
