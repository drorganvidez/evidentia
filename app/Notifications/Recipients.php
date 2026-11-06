<?php

namespace App\Notifications;

use App\Models\Coordinator;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * The people in charge a notice can reach: the coordinators and secretaries of a committee, who must still hold the
 * role, and the management.
 */
final class Recipients
{
    /** @return Collection<int, User> */
    public static function coordinators(?int $committeeId, ?int $except = null): Collection
    {
        return self::holding('COORDINATOR', Coordinator::query()->where('committee_id', $committeeId)->pluck('user_id')->all(), $except);
    }

    /** @return Collection<int, User> */
    public static function secretaries(?int $committeeId, ?int $except = null): Collection
    {
        return self::holding('SECRETARY', Secretary::query()->where('committee_id', $committeeId)->pluck('user_id')->all(), $except);
    }

    /** @return Collection<int, User> */
    public static function managers(): Collection
    {
        return User::query()
            ->whereHas('roles', fn (Builder $roles) => $roles->whereIn('rol', ['PRESIDENT', 'LECTURE']))
            ->with('roles')
            ->get();
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, User>
     */
    private static function holding(string $role, array $ids, ?int $except): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return User::query()
            ->whereKey($ids)
            ->when($except !== null, fn (Builder $query) => $query->whereKeyNot($except))
            ->whereHas('roles', fn (Builder $roles) => $roles->where('rol', $role))
            ->with('roles')
            ->get();
    }
}
