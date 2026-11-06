<?php

namespace App\Support;

/**
 * The before and after of a correction for the activity log: only the fields that changed, with the same keys on both
 * sides.
 */
final class Changes
{
    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array{before?: array<string, mixed>, after?: array<string, mixed>}
     */
    public static function between(array $before, array $after): array
    {
        $changed = array_keys(array_filter(
            $after,
            fn (mixed $value, string $key): bool => ($before[$key] ?? null) !== $value,
            ARRAY_FILTER_USE_BOTH,
        ));

        $pick = fn (array $values): array => array_combine($changed, array_map(fn (string $key): mixed => $values[$key] ?? null, $changed));

        return $changed === [] ? [] : ['before' => $pick($before), 'after' => $pick($after)];
    }

    /** Whether anything changed. */
    public static function any(array $before, array $after): bool
    {
        return self::between($before, $after) !== [];
    }
}
