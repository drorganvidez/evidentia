<?php

namespace App\Enums;

/**
 * Fixed notices always go by mail; important ones by mail unless the person turns it off; informative ones only to
 * the inbox unless the person turns the mail on.
 */
enum NotificationLevel: string
{
    case Fixed = 'fixed';
    case Important = 'important';
    case Informative = 'informative';
}
