<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A reminder or digest already sent to a person, so that it never goes out twice.
 */
class NotificationReminder extends Model
{
    protected $table = 'notification_reminders';

    public $timestamps = false;

    protected $fillable = ['user_id', 'key', 'sent_at'];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
