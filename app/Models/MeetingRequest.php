<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MeetingRequest extends Model
{
    protected $table = 'meeting_requests';

    protected $fillable = [
        'meeting_id',
        'datetime',
        'place',
        'type',
        'modality',
        'committee_id',
        'secretary_id',
        'title',
    ];

    /**
     * Get the diary associated with this meeting request.
     */
    public function diary()
    {
        return $this->hasOne(Diary::class);
    }

    /**
     * Get the committee that owns this meeting request.
     */
    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Get the secretary who made the request.
     */
    public function secretary()
    {
        return $this->belongsTo(Secretary::class);
    }

    /**
     * Get the signature sheet for this meeting request.
     */
    public function signatureSheet()
    {
        return $this->hasOne(SignatureSheet::class);
    }

    /**
     * Get the next 7 upcoming meeting requests.
     */
    public static function nextMeetingRequests()
    {
        return static::where('datetime', '>=', Carbon::now())
            ->orderBy('datetime', 'asc')
            ->limit(7)
            ->get();
    }
}
