<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secretary extends Model
{
    protected $table = 'secretaries';

    /**
     * The committee this secretary belongs to.
     */
    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * The user linked to this secretary.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Default lists created by this secretary.
     */
    public function defaultLists()
    {
        return $this->hasMany(DefaultList::class)->orderByDesc('created_at');
    }

    /**
     * Meeting requests initiated by this secretary.
     */
    public function meetingRequests()
    {
        return $this->hasMany(MeetingRequest::class)->orderByDesc('created_at');
    }

    /**
     * Signature sheets prepared by this secretary.
     */
    public function signatureSheets()
    {
        return $this->hasMany(SignatureSheet::class)->orderByDesc('created_at');
    }

    /**
     * Meeting minutes recorded by this secretary.
     */
    public function meetingMinutes()
    {
        return $this->hasMany(MeetingMinutes::class)->orderByDesc('updated_at');
    }
}
