<?php

namespace Tests\Feature\Activity\WriteRoutes;

use App\Models\Coordinator;
use App\Models\Evidence;
use Tests\TestCase;

/**
 * Write routes of the queue of the presidency (App\Services\ReviewQueue): the pending evidence of the fixtures is
 * written by the coordinator of its committee, so it waits for the presidency. Each route is in exactly one of the
 * four lists.
 */
final class PresidencyReviewWrites
{
    /** @return array<string, array<string, mixed>> */
    public static function writes(): array
    {
        return [
            'president.review.accept' => [
                'role' => 'PRESIDENT',
                'method' => 'POST',
                'setup' => self::byTheCoordinator(...),
                'uri' => fn (array $ids) => route('president.review.accept', ['id' => $ids['evidence']]),
                'expect' => ['evidence.accepted'],
                'acting_role' => 'PRESIDENT',
            ],
            'president.review.reject' => [
                'role' => 'LECTURE',
                'method' => 'POST',
                'setup' => self::byTheCoordinator(...),
                'uri' => fn (array $ids) => route('president.review.reject'),
                'data' => fn (array $ids) => ['_id' => $ids['evidence'], 'reasonrejection' => 'Falta el inventario firmado.'],
                'expect' => ['evidence.rejected'],
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

    private static function byTheCoordinator(TestCase $test, array $ids): void
    {
        $coordinator = Coordinator::where('committee_id', $ids['logistica'])->firstOrFail();
        Evidence::whereKey($ids['evidence'])->update(['user_id' => $coordinator->user_id]);
    }
}
