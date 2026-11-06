<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A conversation addressed to an audience (a role, and the committee for coordination and secretary).
 */
class MessageThread extends Model
{
    protected $table = 'message_threads';

    protected $fillable = [
        'subject',
        'author_id',
        'author_name',
        'author_role',
        'recipient_role',
        'committee_id',
        'about_type',
        'about_id',
        'about_title',
        'state',
        'resolved_at',
        'resolved_by',
        'last_message_at',
        'last_message_by',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at')->orderBy('id');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function reads(): HasMany
    {
        return $this->hasMany(MessageThreadRead::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
