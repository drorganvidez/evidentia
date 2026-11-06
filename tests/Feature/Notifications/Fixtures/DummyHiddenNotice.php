<?php

namespace Tests\Feature\Notifications\Fixtures;

/** Notice of the tests left out of the preferences screen. */
class DummyHiddenNotice extends DummyNotice
{
    public static function key(): string
    {
        return 'test.hidden';
    }

    public static function label(): string
    {
        return 'Aviso oculto de prueba';
    }

    public static function inPreferences(): bool
    {
        return false;
    }

    public static function triggers(): array
    {
        return [];
    }
}
