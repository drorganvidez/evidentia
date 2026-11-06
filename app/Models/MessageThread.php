<?php

namespace App\Models;

use App\Services\Activity\ActivityAccess;
use App\Services\Messaging\Audience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

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

    // Scopes. Every OR stays inside its own where(fn ...), so that a search or a filter never widens what one sees.

    /** Threads addressed to any of the audiences the person belongs to. */
    public function scopeAddressedTo(Builder $query, User $user): void
    {
        $audiences = Audience::of($user);
        if ($audiences->isEmpty()) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $where) use ($audiences): void {
            foreach ($audiences as $audience) {
                $where->orWhere(fn (Builder $one) => $one
                    ->where('recipient_role', $audience->role)
                    ->when($audience->committeeId !== null, fn (Builder $one) => $one->where('committee_id', $audience->committeeId)));
            }
        });
    }

    public function scopeParticipatedBy(Builder $query, User $user): void
    {
        $query->whereHas('messages', fn (Builder $messages) => $messages->where('user_id', $user->getKey()));
    }

    /** The lecturers see every thread; the rest their own, the ones addressed to them and the ones they wrote in. */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->hasRole('LECTURE')) {
            return;
        }

        $query->where(fn (Builder $where) => $where
            ->where('author_id', $user->getKey())
            ->orWhere(fn (Builder $addressed) => $addressed->addressedTo($user))
            ->orWhere(fn (Builder $participated) => $participated->participatedBy($user)));
    }

    /** Threads of others addressed to the person or in which they wrote. */
    public function scopeInbox(Builder $query, User $user): void
    {
        $query->where('author_id', '!=', $user->getKey())
            ->where(fn (Builder $where) => $where
                ->where(fn (Builder $addressed) => $addressed->addressedTo($user))
                ->orWhere(fn (Builder $participated) => $participated->participatedBy($user)));
    }

    /** The last message is someone else's and the person has not read the thread since. */
    public function scopeUnreadFor(Builder $query, User $user): void
    {
        $id = $user->getKey();

        $query->where(fn (Builder $where) => $where->whereNull('last_message_by')->orWhere('last_message_by', '!=', $id))
            ->where(fn (Builder $where) => $where
                ->whereDoesntHave('reads', fn (Builder $reads) => $reads->where('user_id', $id))
                ->orWhereHas('reads', fn (Builder $reads) => $reads
                    ->where('user_id', $id)
                    ->whereColumn('message_thread_reads.read_at', '<', 'message_threads.last_message_at')));
    }

    public function scopeOpen(Builder $query): void
    {
        $query->where('state', 'open');
    }

    public function scopeResolved(Builder $query): void
    {
        $query->where('state', 'resolved');
    }

    /** By subject or author, with the wildcards of the text escaped. */
    public function scopeSearch(Builder $query, string $text): void
    {
        $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], trim($text)).'%';

        $query->where(fn (Builder $where) => $where
            ->whereRaw("subject like ? escape '!'", [$like])
            ->orWhereRaw("author_name like ? escape '!'", [$like]));
    }

    public static function unreadInboxCount(User $user): int
    {
        return static::query()->inbox($user)->unreadFor($user)->count();
    }

    public static function unreadMineCount(User $user): int
    {
        return static::query()->where('author_id', $user->getKey())->unreadFor($user)->count();
    }

    public function isResolved(): bool
    {
        return $this->state === 'resolved';
    }

    public function audience(): Audience
    {
        $perCommittee = in_array($this->recipient_role, ['COORDINATOR', 'SECRETARY'], true) && $this->committee_id !== null;

        return new Audience($this->recipient_role, $perCommittee ? (int) $this->committee_id : null);
    }

    public function audienceLabel(): string
    {
        return $this->audience()->label();
    }

    public function isUnreadFor(User $user): bool
    {
        if ($this->last_message_by !== null && (int) $this->last_message_by === (int) $user->getKey()) {
            return false;
        }

        $readAt = $this->reads()->where('user_id', $user->getKey())->value('read_at');

        return $readAt === null || $this->last_message_at->gt($readAt);
    }

    /**
     * The author, whoever wrote in the thread and the members of its audience.
     *
     * @return Collection<int, User>
     */
    public function participants(): Collection
    {
        $writers = User::whereIn('id', Message::where('message_thread_id', $this->getKey())->whereNotNull('user_id')->select('user_id'))->get();

        return collect([$this->author])
            ->concat($writers)
            ->concat($this->audience()->members())
            ->filter()
            ->unique(fn (User $user) => $user->getKey())
            ->values();
    }

    /** Page of the object the thread is about, if it still exists and the viewer can open it. */
    public function aboutUrl(User $viewer): ?string
    {
        [$route, $id] = match ($this->about_type) {
            'evidence' => ['activity.evidence', $this->visibleEvidenceId($viewer)],
            'meeting_minutes' => ['activity.minutes', MeetingMinutes::whereKey($this->about_id)->value('id')],
            'meeting_request' => ['activity.request', MeetingRequest::whereKey($this->about_id)->value('id')],
            default => [null, null],
        };

        return $route !== null && $id !== null && Route::has($route) ? route($route, ['id' => $id]) : null;
    }

    private function visibleEvidenceId(User $viewer): ?int
    {
        $head = $this->about_id === null ? null : Evidence::find($this->about_id)?->headEvidence();

        return $head !== null && app(ActivityAccess::class)->canViewEvidence($viewer, $head) ? $head->id : null;
    }
}
