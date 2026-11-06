<?php

namespace App\Services\Activity;

use App\Enums\ActivityAction;
use App\Enums\ActivityFlag;
use App\Enums\Deadline;
use App\Events\ActivityRecorded;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\User;
use App\Services\Deadlines;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Writes the annotations of the activity log. Always used through the Activity facade, so that Activity::fake()
 * replaces it in controllers and services alike.
 */
class ActivityRecorder
{
    /** Attribute of the request that keeps the id shared by its annotations. */
    private const REQUEST_ATTRIBUTE = '_activity_request_id';

    public function __construct(protected Deadlines $deadlines) {}

    /**
     * Annotates an action of the signed-in person (or of the given actor).
     *
     * @param  list<ActivityFlag>  $flags
     * @param  iterable<User|int>  $targets
     */
    public function record(
        ActivityAction $action,
        ?Model $subject = null,
        ?User $target = null,
        array $details = [],
        array $flags = [],
        Deadline|false|null $deadline = null,
        iterable $targets = [],
        ?string $actingRole = null,
        ?User $actor = null,
        ?string $subjectTitle = null,
        int|false|null $committeeId = null,
    ): ActivityLog {
        $actor ??= Auth::user() instanceof User ? Auth::user() : null;
        $actingRole ??= ActingRole::resolve($actor, request()->route());

        $deadline ??= $action->deadline();
        if ($deadline instanceof Deadline && ! $this->deadlines->isOpen($deadline)) {
            $flags[] = ActivityFlag::AfterDeadline;
        }

        return $this->write($action, $actor, $actingRole, $subject, $target, $details, $flags, $targets, $subjectTitle, $committeeId);
    }

    /**
     * Annotation of the system: no actor and no role.
     *
     * @param  iterable<User|int>  $targets
     */
    public function system(
        ActivityAction $action,
        ?Model $subject = null,
        ?User $target = null,
        array $details = [],
        iterable $targets = [],
        ?string $subjectTitle = null,
        int|false|null $committeeId = null,
    ): ActivityLog {
        return $this->write($action, null, null, $subject, $target, $details, [], $targets, $subjectTitle, $committeeId);
    }

    /** Id shared by the annotations of the current request (or of the console process). */
    public function requestId(): string
    {
        $request = request();
        $id = $request->attributes->get(self::REQUEST_ATTRIBUTE);

        return is_string($id) ? $id : $this->startRequest();
    }

    /** Starts a new request id (seeders and commands that group by logical unit). */
    public function startRequest(): string
    {
        $id = (string) Str::uuid();
        request()->attributes->set(self::REQUEST_ATTRIBUTE, $id);

        return $id;
    }

    /**
     * @param  list<ActivityFlag|string>  $flags
     * @param  iterable<User|int>  $targets
     */
    protected function write(
        ActivityAction $action,
        ?User $actor,
        ?string $actingRole,
        ?Model $subject,
        ?User $target,
        array $details,
        array $flags,
        iterable $targets,
        ?string $subjectTitle,
        int|false|null $committeeId,
    ): ActivityLog {
        $committeeId = match (true) {
            $committeeId === false => null,
            $committeeId === null => $subject ? SubjectInfo::committeeId($subject) : null,
            default => $committeeId,
        };
        $flags = array_values(array_unique(array_map(
            fn (ActivityFlag|string $flag): string => $flag instanceof ActivityFlag ? $flag->value : $flag,
            $flags,
        )));
        $details = SubjectInfo::sanitize($details);
        // las peticiones HTTP y los tests llevan red; la consola real no
        $network = ! app()->runningInConsole() || app()->runningUnitTests();

        $attributes = [
            'created_at' => now(),
            'request_id' => $this->requestId(),
            'user_id' => $actor?->getKey(),
            'user_name' => $actor ? self::limit(SubjectInfo::personName($actor)) : null,
            'user_username' => $actor?->username,
            'acting_role' => $actingRole,
            'action' => $action->value,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'subject_root_id' => $subject ? SubjectInfo::rootId($subject) : null,
            'subject_title' => self::limit($subjectTitle ?? ($subject ? SubjectInfo::title($subject) : null)),
            'target_user_id' => $target?->getKey(),
            'target_name' => $target ? self::limit(SubjectInfo::personName($target)) : null,
            'target_username' => $target?->username,
            'committee_id' => $committeeId,
            'committee_name' => $committeeId === null ? null : Committee::query()->whereKey($committeeId)->value('name'),
            'details' => $details === [] ? null : $details,
            'flags' => $flags === [] ? null : $flags,
            'flagged' => $flags !== [],
            'ip' => $network ? request()->ip() : null,
            'user_agent' => $network ? self::limit(request()->userAgent()) : null,
        ];

        $targetIds = collect($targets)
            ->map(fn (User|int|null $user): ?int => $user instanceof User ? (int) $user->getKey() : $user)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $this->persist($attributes, $targetIds);
    }

    /** Cuts a copied text to the length of its column. */
    private static function limit(?string $value): ?string
    {
        return $value === null ? null : mb_substr($value, 0, 255);
    }

    /** @param  list<int>  $targetIds */
    protected function persist(array $attributes, array $targetIds): ActivityLog
    {
        $log = ActivityLog::create($attributes);

        foreach (array_chunk($targetIds, 500) as $chunk) {
            DB::table('activity_log_targets')->insert(array_map(
                fn (int $userId): array => ['activity_log_id' => $log->id, 'user_id' => $userId],
                $chunk,
            ));
        }

        event(new ActivityRecorded($log));

        return $log;
    }
}
