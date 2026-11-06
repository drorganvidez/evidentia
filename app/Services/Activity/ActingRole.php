<?php

namespace App\Services\Activity;

use App\Http\Middleware\CheckRoles;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Routing\Route;

/**
 * The role someone acts with comes from the route they used, not from the roles they have.
 */
final class ActingRole
{
    /** Routes where one acts as the author, with or without the STUDENT role */
    public const AUTHOR = ['evidence.', 'meeting.list', 'attendee.list'];

    /** Routes without a role (Messaging sets the role of the messages) */
    public const NEUTRAL = ['profile.', 'password.', 'sign', 'logout', 'activity.mine', 'notifications.', 'messages.'];

    /** Prefix of the route name → candidate role */
    public const PREFIXES = [
        'coordinator.' => 'COORDINATOR',
        'secretary.' => 'SECRETARY',
        'gp.' => 'SECRETARY',
        'registercoordinator.' => 'REGISTER_COORDINATOR',
        'attendee.' => 'REGISTER_COORDINATOR',
        'president.' => 'PRESIDENT',
        'lecture.' => 'LECTURE',
        'randomize.' => 'LECTURE',
        'management.user.delete.all' => 'LECTURE',
    ];

    public static function resolve(?User $user, ?Route $route): ?string
    {
        $name = $route?->getName();
        if ($user === null || $name === null) {
            return null;
        }

        if (self::matches($name, self::NEUTRAL)) {
            return null;
        }

        // antes que los prefijos: attendee.list es del autor
        if (self::matches($name, self::AUTHOR)) {
            return 'STUDENT';
        }

        foreach (self::PREFIXES as $prefix => $role) {
            if (str_starts_with($name, $prefix) && $user->hasRole($role)) {
                return $role;
            }
        }

        // sin candidato o sin ese rol, los roles de CheckRoles que tenga, en orden de prioridad
        $allowed = self::checkedRoles($route);
        foreach (Roles::PRIORITY as $role) {
            if (in_array($role, $allowed, true) && $user->hasRole($role)) {
                return $role;
            }
        }

        return null;
    }

    /** @param  list<string>  $prefixes */
    private static function matches(string $name, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parameters of every CheckRoles middleware of the route, the ones of the controller constructor included.
     *
     * @return list<string>
     */
    private static function checkedRoles(Route $route): array
    {
        $roles = [];
        foreach ($route->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, CheckRoles::class.':')) {
                $roles = [...$roles, ...explode(',', substr($middleware, strlen(CheckRoles::class) + 1))];
            }
        }

        return array_values(array_unique(array_map('trim', $roles)));
    }
}
