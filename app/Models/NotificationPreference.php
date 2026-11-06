<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mail preferences of a person: only where they differ from the level of each notice, plus the daily digest.
 */
class NotificationPreference extends Model
{
    protected $table = 'notification_preferences';

    protected $fillable = ['user_id', 'mail', 'digest'];

    protected function casts(): array
    {
        return [
            'mail' => 'array',
            'digest' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
