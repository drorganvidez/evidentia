<?php

namespace Tests\Feature\Activity\WriteRoutes;

/**
 * Write routes of the record of a person for the management. Each route is in exactly one of the four lists.
 */
final class ProfileRecordWrites
{
    /** @return array<string, array<string, mixed>> */
    public static function writes(): array
    {
        return [
            'profiles.export' => [
                'role' => 'LECTURE',
                'method' => 'GET',
                'uri' => fn (array $ids) => route('profiles.export', ['id' => $ids['student']]),
                'expect' => ['export.record'],
                'acting_role' => 'LECTURE',
            ],
        ];
    }

    /** @return array<string, string> route => reason */
    public static function excluded(): array
    {
        return [];
    }

    /** @return array<string, string> route => test class that checks its annotation */
    public static function coveredElsewhere(): array
    {
        return [];
    }

    /** @return array<string, string> route => batch that will annotate it */
    public static function pending(): array
    {
        return [];
    }
}
