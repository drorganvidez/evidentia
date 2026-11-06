<?php

namespace Tests\Feature\Notifications\Fixtures;

use App\Enums\NotificationLevel;

/** Fixed notice of the tests, only for students. */
class DummyFixedNotice extends DummyNotice
{
    public static function key(): string
    {
        return 'test.fixed';
    }

    public static function label(): string
    {
        return 'Aviso fijo de prueba';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Fixed;
    }

    public static function audience(): array
    {
        return ['STUDENT'];
    }

    public static function triggers(): array
    {
        return [];
    }
}
