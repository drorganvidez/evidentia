<?php

namespace App\Models;

use App\Enums\ActivityAction;
use App\Enums\ActivityFlag;
use App\Services\Activity\SubjectInfo;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
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

    /** The verb as an enum; null for a verb retired after it was written (the log keeps five courses). */
    public function actionEnum(): ?ActivityAction
    {
        return ActivityAction::tryFrom((string) $this->action);
    }

    /** @return list<ActivityFlag> */
    public function flagList(): array
    {
        return array_values(array_filter(array_map(
            fn ($flag): ?ActivityFlag => is_string($flag) ? ActivityFlag::tryFrom($flag) : null,
            $this->flags ?? [],
        )));
    }

    // Scopes. Every OR stays inside its own where(fn ...), so that no other condition of the query can be bypassed.

    /** Annotations where the person is the actor, the affected person or one of the affected people. */
    public function scopeForUser(Builder $query, User|int $user): void
    {
        $id = $user instanceof User ? $user->getKey() : $user;

        $query->where(fn (Builder $where) => $where
            ->where('user_id', $id)
            ->orWhere('target_user_id', $id)
            ->orWhereIn('id', DB::table('activity_log_targets')->select('activity_log_id')->where('user_id', $id)));
    }

    /** History of a subject; every version of an evidence shares the one of its first version. */
    public function scopeAbout(Builder $query, Model $subject): void
    {
        $query->where('subject_type', $subject->getMorphClass());

        if ($subject instanceof Evidence) {
            $query->where('subject_root_id', SubjectInfo::rootId($subject));
        } else {
            $query->where('subject_id', $subject->getKey());
        }
    }

    /** @param  list<int>  $ids */
    public function scopeInCommittees(Builder $query, array $ids): void
    {
        $query->whereIn('committee_id', $ids);
    }

    /** A whole group (group:evidence) or a single verb; anything else is ignored. */
    public function scopeOfType(Builder $query, string $type): void
    {
        if (str_starts_with($type, 'group:') && array_key_exists(substr($type, 6), ActivityAction::groups())) {
            $query->whereIn('action', ActivityAction::inGroup(substr($type, 6)));
        } elseif (ActivityAction::tryFrom($type) !== null) {
            $query->where('action', $type);
        }
    }

    public function scopeWithoutNoise(Builder $query): void
    {
        $query->whereNotIn('action', ActivityAction::noisy());
    }

    public function scopeAccess(Builder $query): void
    {
        $query->whereIn('action', ActivityAction::inGroup('access'));
    }

    public function scopeBetween(Builder $query, ?CarbonInterface $from, ?CarbonInterface $to): void
    {
        $query->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->when($to, fn (Builder $query) => $query->where('created_at', '<=', $to));
    }

    /**
     * What a history shows to someone who does not see it whole: no notices sent to others and, on an evidence,
     * nothing annotated while it was a draft (those rows have no committee) except the notices sent to the viewer.
     */
    public function scopeHistoryVisibleTo(Builder $query, User $viewer, bool $full): void
    {
        if ($full) {
            return;
        }

        $id = $viewer->getKey();
        $query->where(fn (Builder $where) => $where
            ->where('action', '!=', ActivityAction::NotificationSent->value)
            ->orWhere('target_user_id', $id));
        $query->where(fn (Builder $where) => $where
            ->whereNull('subject_type')
            ->orWhere('subject_type', '!=', (new Evidence)->getMorphClass())
            ->orWhereNotNull('committee_id')
            ->orWhere(fn (Builder $own) => $own
                ->where('action', ActivityAction::NotificationSent->value)
                ->where('target_user_id', $id)));
    }
}
