<?php

namespace App\Facades;

use App\Services\Activity\ActivityFake;
use App\Services\Activity\ActivityRecorder;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Models\ActivityLog record(\App\Enums\ActivityAction $action, ?\Illuminate\Database\Eloquent\Model $subject = null, ?\App\Models\User $target = null, array $details = [], array $flags = [], \App\Enums\Deadline|false|null $deadline = null, iterable $targets = [], ?string $actingRole = null, ?\App\Models\User $actor = null, ?string $subjectTitle = null, int|false|null $committeeId = null)
 * @method static \App\Models\ActivityLog system(\App\Enums\ActivityAction $action, ?\Illuminate\Database\Eloquent\Model $subject = null, ?\App\Models\User $target = null, array $details = [], iterable $targets = [], ?string $subjectTitle = null, int|false|null $committeeId = null)
 * @method static string requestId()
 * @method static string startRequest()
 *
 * @see ActivityRecorder
 */
class Activity extends Facade
{
    /** Replaces the recorder with one that keeps the annotations in memory. */
    public static function fake(): ActivityFake
    {
        $fake = app(ActivityFake::class);
        static::swap($fake);

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return ActivityRecorder::class;
    }
}
