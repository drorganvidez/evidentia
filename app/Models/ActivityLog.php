<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

/**
 * One annotation of the activity log. Only the recorder writes it and nothing in the app edits or deletes it:
 * the purge of activity:prune goes through the query builder.
 */
class ActivityLog extends Model
{
    protected $table = 'activity_log';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'details' => 'array',
            'flags' => 'array',
            'flagged' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('activity_log solo admite inserciones'));
        static::deleting(fn () => throw new LogicException('activity_log solo admite inserciones'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function targets(): HasMany
    {
        return $this->hasMany(ActivityLogTarget::class);
    }
}
