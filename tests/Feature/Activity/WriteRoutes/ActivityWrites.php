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
            'evidence.move' => [
                'role' => 'STUDENT',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('evidence.move'),
                'data' => fn (array $ids) => ['_id' => $ids['evidence'], 'committee' => $ids['programa']],
                'expect' => ['evidence.moved'],
                'acting_role' => 'STUDENT',
            ],
            'coordinator.evidence.move' => [
                'role' => 'COORDINATOR',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('coordinator.evidence.move'),
                'data' => fn (array $ids) => ['_id' => $ids['evidence'], 'committee' => $ids['programa']],
                'expect' => ['evidence.moved'],
                'acting_role' => 'COORDINATOR',
            ],
            'management.evidence.move' => [
                'role' => 'LECTURE',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('management.evidence.move'),
                'data' => fn (array $ids) => ['_id' => $ids['evidence'], 'committee' => $ids['programa']],
                'expect' => ['evidence.moved'],
                'acting_role' => 'LECTURE',
            ],
            'management.evidence.review' => [
                'role' => 'LECTURE',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('management.evidence.review'),
                'data' => fn (array $ids) => ['_id' => $ids['evidence'], 'decision' => 'reject', 'reasonrejection' => 'Faltan las fotos del montaje.'],
                'expect' => ['evidence.rejected'],
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
        return [];
    }
}
