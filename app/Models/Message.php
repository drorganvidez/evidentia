<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One plain text message of a thread, with the name and the role of whoever wrote it.
 */
class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = ['message_thread_id', 'user_id', 'user_name', 'role', 'body'];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'message_thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
