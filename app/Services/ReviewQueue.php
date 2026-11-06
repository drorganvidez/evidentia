<?php

namespace App\Services;

use App\Models\Committee;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\User;
use App\Notifications\Recipients;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

/**
 * Who reviews each evidence and which queue each person sees; nothing else decides it. The coordinators of a
 * committee review what is sent to it, except what one of them wrote: that goes to the presidency, so nobody reviews
 * the coordination they belong to. The presidency (whoever holds the PRESIDENT role) reviews what is sent to its own
 * committee, the one marked in committees.presidency, as its coordinators, and what the coordinators send to the
 * committee they coordinate. Nobody reviews their own evidence (App\Services\SelfReview). The lecturer may review
 * for the presidency as for everything else, but no queue is theirs.
 */
final class ReviewQueue
{
    public const COORDINATION = 'coordination';

    public const PRESIDENCY = 'presidency';

    /** Days an evidence may wait in a queue before it is late */
    public const LATE_DAYS = 7;

    private ?Committee $presidency = null;

    private bool $resolved = false;

    /** The committee of the presidency, or null if there is none. */
    public function presidency(): ?Committee
    {
        if (! $this->resolved) {
            $this->presidency = Committee::presidency();
            $this->resolved = true;
        }

        return $this->presidency;
    }

    public function isPresidency(Committee|int|null $committee): bool
    {
        $id = $committee instanceof Committee ? $committee->getKey() : $committee;
        $presidency = $this->presidency();

        return $id !== null && $presidency !== null && (int) $id === (int) $presidency->getKey();
    }

    /** Whether the author coordinates, still holding the role, the committee the evidence is sent to. */
    public function authorCoordinates(Evidence $evidence): bool
    {
        return $evidence->committee_id !== null && Coordinator::query()
            ->where('user_id', $evidence->user_id)
            ->where('committee_id', $evidence->committee_id)
            ->whereHas('user.roles', fn (Builder $roles) => $roles->where('rol', 'COORDINATOR'))
            ->exists();
    }

    /** Whether the presidency reviews the evidence: sent to its committee or written by a coordinator of its committee. */
    public function goesToPresidency(Evidence $evidence): bool
    {
        return $this->isPresidency($evidence->committee_id) || $this->authorCoordinates($evidence);
    }

    /** Whether the person reviews the evidence as a coordinator of its committee. */
    public function reviewsAsCoordinator(User $user, Evidence $evidence): bool
    {
        return $this->reviewable($user, $evidence)
            && $user->hasRole('COORDINATOR')
            && $evidence->committee_id !== null
            && (int) $user->coordinator?->committee_id === (int) $evidence->committee_id
            && ! $this->authorCoordinates($evidence);
    }

    /** Whether the person reviews the evidence for the presidency: a president or the lecturer. */
    public function reviewsAsPresidency(User $user, Evidence $evidence): bool
    {
        return $this->reviewable($user, $evidence)
            && ($user->hasRole('PRESIDENT') || $user->hasRole('LECTURE'))
            && $this->goesToPresidency($evidence);
    }

    public function canReview(User $user, Evidence $evidence): bool
    {
        return $this->reviewsAsCoordinator($user, $evidence) || $this->reviewsAsPresidency($user, $evidence);
    }

    /**
     * Who reviews the evidence in their queue, never its author: the presidents when it goes to the presidency and the
     * coordinators of its committee unless one of them wrote it.
     *
     * @return Collection<int, User>
     */
    public function reviewers(Evidence $evidence): Collection
    {
        $people = $this->goesToPresidency($evidence) ? $this->presidents() : collect();
        if ($evidence->committee_id !== null && ! $this->authorCoordinates($evidence)) {
            $people = $people->concat(Recipients::coordinators((int) $evidence->committee_id));
        }

        return $people->reject(fn (User $user): bool => (int) $user->getKey() === (int) $evidence->user_id)
            ->unique(fn (User $user) => $user->getKey())
            ->values();
    }

    /** @return Collection<int, User> */
    public function presidents(): Collection
    {
        return User::query()
            ->whereHas('roles', fn (Builder $roles) => $roles->where('rol', 'PRESIDENT'))
            ->with('roles')
            ->orderBy('id')
            ->get();
    }

    /**
     * Everybody with a queue of their own: the coordinators with a committee and the presidents.
     *
     * @return Collection<int, User>
     */
    public function reviewersWithQueue(): Collection
    {
        return User::query()
            ->where(fn (Builder $query) => $query
                ->whereHas('roles', fn (Builder $roles) => $roles->where('rol', 'PRESIDENT'))
                ->orWhere(fn (Builder $query) => $query
                    ->whereHas('roles', fn (Builder $roles) => $roles->where('rol', 'COORDINATOR'))
                    ->whereHas('coordinator.committee')))
            ->with(['roles', 'coordinator.committee'])
            ->orderBy('id')
            ->get();
    }

    /**
     * The queues of the person, the coordination of their committee first: what each one holds (last versions sent
     * for review, never the person's own), its name and the route of its pending tab.
     *
     * @return list<array{key: string, kind: string, name: string, committee: ?Committee, route: string, evidences: Builder}>
     */
    public function queuesOf(User $user): array
    {
        $queues = [];

        $committee = $user->hasRole('COORDINATOR') ? $user->coordinator?->committee : null;
        if ($committee !== null) {
            $queues[] = [
                'key' => self::COORDINATION.':'.$committee->getKey(),
                'kind' => self::COORDINATION,
                'name' => (string) $committee->name,
                'committee' => $committee,
                'route' => self::routes(self::COORDINATION)['pending'],
                'evidences' => $this->forCommittee($committee)->where('user_id', '!=', $user->getKey()),
            ];
        }

        if ($user->hasRole('PRESIDENT')) {
            $presidency = $this->presidency();
            $queues[] = [
                'key' => self::PRESIDENCY,
                'kind' => self::PRESIDENCY,
                'name' => (string) ($presidency?->name ?? 'Presidencia'),
                'committee' => $presidency,
                'route' => self::routes(self::PRESIDENCY)['pending'],
                'evidences' => $this->forPresidency()->where('user_id', '!=', $user->getKey()),
            ];
        }

        return $queues;
    }

    /** The queue of one kind of the person, or null when it is not theirs. */
    public function queueOf(User $user, string $kind): ?array
    {
        foreach ($this->queuesOf($user) as $queue) {
            if ($queue['kind'] === $kind) {
                return $queue;
            }
        }

        return null;
    }

    /** Pending evidences of a queue of queuesOf(). */
    public static function pending(array $queue): Builder
    {
        return (clone $queue['evidences'])->where('status', 'PENDING');
    }

    /** Last versions sent to the committee that its coordination reviews: none written by one of its coordinators. */
    public function forCommittee(Committee|int $committee): Builder
    {
        return $this->coordination($this->sent()->where('committee_id', self::id($committee)));
    }

    /** Last versions sent for review that the presidency reviews. */
    public function forPresidency(): Builder
    {
        return $this->presidencyScope($this->sent());
    }

    /** Last versions sent to the committee that go to the presidency because one of its coordinators wrote them. */
    public function delegatedFrom(Committee|int $committee): Builder
    {
        $query = $this->sent()->where('committee_id', self::id($committee));

        return $query->whereExists($this->writtenByACoordinator($query));
    }

    /** Narrows a query of evidences to those the coordination of their committee reviews. */
    public function coordination(Builder $evidences): Builder
    {
        return $evidences->whereNotExists($this->writtenByACoordinator($evidences));
    }

    /** Narrows a query of evidences to those the presidency reviews. */
    public function presidencyScope(Builder $evidences): Builder
    {
        $presidency = $this->presidency()?->getKey();
        $committee = $evidences->qualifyColumn('committee_id');

        return $evidences->where(fn (Builder $query) => $query
            ->when($presidency !== null, fn (Builder $query) => $query->where($committee, $presidency))
            ->orWhereExists($this->writtenByACoordinator($evidences)));
    }

    /**
     * Routes of the review screens of a kind of queue.
     *
     * @return array{all: string, pending: string, accepted: string, rejected: string, view: string, accept: string, reject: string}
     */
    public static function routes(string $kind): array
    {
        $prefix = $kind === self::PRESIDENCY ? 'president.review' : 'coordinator.evidence';
        $list = $kind === self::PRESIDENCY ? 'president.review' : 'coordinator.evidence.list';

        return [
            'all' => $list.'.all',
            'pending' => $list.'.pending',
            'accepted' => $list.'.accepted',
            'rejected' => $list.'.rejected',
            'view' => $prefix.'.view',
            'accept' => $prefix.'.accept',
            'reject' => $prefix.'.reject',
        ];
    }

    /** The last version of an evidence sent for review that the person did not write. */
    private function reviewable(User $user, Evidence $evidence): bool
    {
        // a model just created does not hold the defaults of the table, where last starts true
        return (bool) ($evidence->last ?? true)
            && $evidence->status !== 'DRAFT'
            && (int) $evidence->user_id !== (int) $user->getKey();
    }

    private function sent(): Builder
    {
        return Evidence::query()->where('last', true)->where('status', '!=', 'DRAFT');
    }

    /** Subquery: the author of the evidence coordinates, holding the role, the committee it is sent to. */
    private function writtenByACoordinator(Builder $evidences): Closure
    {
        $author = $evidences->qualifyColumn('user_id');
        $committee = $evidences->qualifyColumn('committee_id');

        return fn (QueryBuilder $query) => $query->selectRaw('1')->from('coordinators')
            ->whereColumn('coordinators.user_id', $author)
            ->whereColumn('coordinators.committee_id', $committee)
            ->whereExists(fn (QueryBuilder $roles) => $roles->selectRaw('1')->from('role_user')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->whereColumn('role_user.user_id', 'coordinators.user_id')
                ->where('roles.rol', 'COORDINATOR'));
    }

    private static function id(Committee|int $committee): int
    {
        return (int) ($committee instanceof Committee ? $committee->getKey() : $committee);
    }
}
