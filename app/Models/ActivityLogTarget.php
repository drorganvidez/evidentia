<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A person affected by an annotation when there are several. Read only: the recorder inserts them in bulk.
 */
class ActivityLogTarget extends Model
{
    protected $table = 'activity_log_targets';

    protected $primaryKey = 'activity_log_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ActivityLog::class, 'activity_log_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
