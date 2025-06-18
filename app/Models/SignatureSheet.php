<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureSheet extends Model
{
    use HasFactory;

    protected $table = 'signature_sheets';

    protected $fillable = [
        'title',
        'random_identifier',
        'meeting_request_id',
        'secretary_id',
    ];

    /**
     * The users who signed this sheet.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->orderByDesc('created_at');
    }

    /**
     * The meeting request associated with this signature sheet.
     */
    public function meetingRequest()
    {
        return $this->belongsTo(MeetingRequest::class);
    }

    /**
     * The secretary who created this signature sheet.
     */
    public function secretary()
    {
        return $this->belongsTo(Secretary::class);
    }
}
