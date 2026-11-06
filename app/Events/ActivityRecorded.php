<?php

namespace App\Events;

use App\Models\ActivityLog;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

/**
 * An annotation was written. Waits for the commit when there is a transaction open and never fires if it rolls back.
 */
final class ActivityRecorded implements ShouldDispatchAfterCommit
{
    public function __construct(public readonly ActivityLog $activity) {}
}
