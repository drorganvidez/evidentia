<?php

namespace Tests\Feature\Activity\WriteRoutes;

use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Tests\TestCase;

/**
 * Write routes of the message threads (routes/messages.php).
 */
final class MessagesWrites
{
    /** A thread of the student to the coordination of Logística, written straight to the tables (no annotation). */
    private static function thread(string $state = 'open'): \Closure
    {
        return function (TestCase $test, array $ids) use ($state): void {
            $student = User::find($ids['student']);
            $thread = MessageThread::create([
                'subject' => 'Duda sobre las mesas', 'author_id' => $student->id, 'author_name' => $student->name,
                'author_role' => 'STUDENT', 'recipient_role' => 'COORDINATOR', 'committee_id' => $ids['logistica'],
                'state' => $state, 'last_message_at' => now(), 'last_message_by' => $student->id,
            ]);
            Message::create([
                'message_thread_id' => $thread->id, 'user_id' => $student->id, 'user_name' => $student->name,
                'role' => 'STUDENT', 'body' => '¿Cuento el montaje de las mesas?',
            ]);
        };
    }

    private static function onThread(string $route, string $verb, string $state = 'open', array $data = []): array
    {
        return [
            'role' => 'COORDINATOR',
            'method' => 'POST',
            'setup' => self::thread($state),
            'uri' => fn (array $ids) => route($route, MessageThread::latest('id')->firstOrFail()),
            'data' => fn (array $ids) => $data,
            'expect' => [$verb],
            'acting_role' => 'COORDINATOR',
        ];
    }

    public static function writes(): array
    {
        return [
            'messages.store' => [
                'role' => 'STUDENT',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('messages.store'),
                'data' => fn (array $ids) => [
                    'recipient' => 'COORDINATOR:'.$ids['logistica'], 'subject' => 'Duda sobre las mesas',
                    'body' => '¿Cuento el montaje de las mesas?',
                ],
                'expect' => ['message.sent'],
                'acting_role' => 'STUDENT',
            ],
            'messages.reply' => self::onThread('messages.reply', 'message.replied', data: ['body' => 'Sí, cuéntalo.']),
            'messages.resolve' => self::onThread('messages.resolve', 'message.resolved'),
            'messages.reopen' => self::onThread('messages.reopen', 'message.reopened', 'resolved'),
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
