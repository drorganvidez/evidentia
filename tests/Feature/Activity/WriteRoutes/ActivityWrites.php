<?php

namespace Tests\Feature\Activity\WriteRoutes;

/**
 * Write routes of the activity log and of the corrections of an evidence (routes/activity.php).
 */
final class ActivityWrites
{
    public static function writes(): array
    {
        return [
            'activity.export' => [
                'role' => 'LECTURE',
                'method' => 'GET',
                'uri' => fn (array $ids) => route('activity.export'),
                'expect' => ['export.activity'],
                'acting_role' => 'LECTURE',
            ],
        ];
    }

    /** @return array<string, string> */
    public static function excluded(): array
    {
        return [];
    }

    /** @return array<string, string> */
    public static function coveredElsewhere(): array
    {
        return [];
    }

    /** @return array<string, string> */
    public static function pending(): array
    {
        return [
            'evidence.move' => 'P',
            'coordinator.evidence.move' => 'P',
            'management.evidence.move' => 'P',
            'management.evidence.review' => 'P',
        ];
    }
}
