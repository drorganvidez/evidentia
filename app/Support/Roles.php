<?php

namespace App\Support;

use App\Models\User;

/**
 * Names and order of the roles. The labels never come from roles.slug, which differs between the seeder and the tests.
 */
final class Roles
{
    public const PRIORITY = ['LECTURE', 'PRESIDENT', 'REGISTER_COORDINATOR', 'COORDINATOR', 'SECRETARY', 'STUDENT'];

    public const RESPONSIBLE = ['COORDINATOR', 'SECRETARY', 'REGISTER_COORDINATOR', 'PRESIDENT', 'LECTURE'];

    public const LABELS = [
        'STUDENT' => 'Estudiante',
        'COORDINATOR' => 'Coordinador',
        'SECRETARY' => 'Secretario',
        'REGISTER_COORDINATOR' => 'Coordinador de registro',
        'PRESIDENT' => 'Presidente',
        'LECTURE' => 'Profesor',
    ];

    public static function label(?string $role): string
    {
        if ($role === null) {
            return '—';
        }

        return self::LABELS[$role] ?? $role;
    }

    /** «como coordinador», «como secretario de Logística»; null without a role. */
    public static function asLabel(?string $role, ?string $committee = null): ?string
    {
        if ($role === null) {
            return null;
        }

        $label = 'como '.mb_strtolower(self::label($role));

        return filled($committee) ? $label.' de '.$committee : $label;
    }

    /** The first role of PRIORITY the person has. */
    public static function highest(User $user): ?string
    {
        foreach (self::PRIORITY as $role) {
            if ($user->hasRole($role)) {
                return $role;
            }
        }

        return null;
    }

    /**
     * Committees the person coordinates or is secretary of.
     *
     * @return list<int>
     */
    public static function committeeIds(User $user): array
    {
        $ids = [
            $user->hasRole('COORDINATOR') ? $user->coordinator?->committee_id : null,
            $user->hasRole('SECRETARY') ? $user->secretary?->committee_id : null,
        ];

        return array_values(array_unique(array_map('intval', array_filter($ids, fn ($id): bool => $id !== null))));
    }

    public static function isManager(User $user): bool
    {
        return $user->hasRole('PRESIDENT') || $user->hasRole('LECTURE');
    }
}
