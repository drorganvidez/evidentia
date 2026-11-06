<?php

namespace Tests\Feature\Notifications\Fixtures;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;

/** Informative notice of the tests, only for coordinators. */
class DummyInformativeNotice extends DummyNotice
{
    public static function key(): string
    {
        return 'test.informative';
    }

    public static function label(): string
    {
        return 'Aviso informativo de prueba';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Informative;
    }

    public static function audience(): array
    {
        return ['COORDINATOR'];
    }

    public static function triggers(): array
    {
        return [ActivityAction::EvidenceAccepted, ActivityAction::EvidenceRejected];
    }
}
