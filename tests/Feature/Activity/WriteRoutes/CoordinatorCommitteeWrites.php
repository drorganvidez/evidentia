<?php

namespace Tests\Feature\Activity\WriteRoutes;

/**
 * Write routes of «Mi comité», the workspace of the coordinator. Each route is in exactly one of the four lists.
 */
final class CoordinatorCommitteeWrites
{
    /** @return array<string, array<string, mixed>> */
    public static function writes(): array
    {
        return [
            'coordinator.committee.export' => [
                'role' => 'COORDINATOR',
                'method' => 'GET',
                'uri' => fn (array $ids) => route('coordinator.committee.export'),
                'expect' => ['committee.exported'],
                'acting_role' => 'COORDINATOR',
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
