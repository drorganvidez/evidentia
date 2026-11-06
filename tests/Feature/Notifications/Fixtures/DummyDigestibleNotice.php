<?php

namespace Tests\Feature\Notifications\Fixtures;

use App\Notifications\Digestible;

/** Important notice of the tests that the daily digest replaces. */
class DummyDigestibleNotice extends DummyNotice implements Digestible
{
    public static function key(): string
    {
        return 'test.digestible';
    }

    public static function label(): string
    {
        return 'Aviso con resumen de prueba';
    }

    public static function audience(): array
    {
        return ['COORDINATOR', 'SECRETARY'];
    }

    public static function triggers(): array
    {
        return [];
    }
}
