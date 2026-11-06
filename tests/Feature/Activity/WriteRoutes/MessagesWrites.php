<?php

namespace Tests\Feature\Activity\WriteRoutes;

/**
 * Write routes of the message threads (routes/messages.php).
 */
final class MessagesWrites
{
    public static function writes(): array
    {
        return [];
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
            'messages.store' => 'B',
            'messages.reply' => 'B',
            'messages.resolve' => 'B',
            'messages.reopen' => 'B',
        ];
    }
}
