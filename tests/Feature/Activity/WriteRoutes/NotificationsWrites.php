<?php

namespace Tests\Feature\Activity\WriteRoutes;

/**
 * Write routes of the inbox of the notices and of their preferences. Each route is in exactly one of the four lists.
 */
final class NotificationsWrites
{
    /** @return array<string, array<string, mixed>> */
    public static function writes(): array
    {
        return [];
    }

    /** @return array<string, string> route => reason */
    public static function excluded(): array
    {
        return [
            'notifications.read_all' => 'Marca de lectura de los avisos propios, sin valor probatorio',
            'notifications.open' => 'Marca de lectura de un aviso propio, sin valor probatorio',
        ];
    }

    /** @return array<string, string> route => test class that checks its annotation */
    public static function coveredElsewhere(): array
    {
        return [];
    }

    /** @return array<string, string> route => batch that instruments it */
    public static function pending(): array
    {
        return [];
    }
}
