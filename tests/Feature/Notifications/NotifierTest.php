<?php

namespace Tests\Feature\Notifications;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Notifications\Notifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\Feature\Notifications\Fixtures\DummyNotice;
use Tests\Feature\Notifications\Fixtures\FlakyTransport;
use Tests\TestCase;

/**
 * A mail server that fails for one person never stops the notice for the rest nor breaks the request.
 */
class NotifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_failed_mail_does_not_stop_the_next_person(): void
    {
        Exceptions::fake();
        $first = User::factory()->create(['email' => 'caido@example.com']);
        $second = User::factory()->create(['email' => 'bien@example.com']);
        $transport = new FlakyTransport('caido@example.com');
        Mail::extend('flaky', fn () => $transport);
        config(['mail.mailers.flaky' => ['transport' => 'flaky'], 'mail.default' => 'flaky']);

        $sent = app(Notifier::class)->send([$first, $second], new DummyNotice);

        $this->assertSame(1, $sent);
        $this->assertSame(['bien@example.com'], $transport->delivered);
        $this->assertSame(1, DB::table('notifications')->where('notifiable_id', $second->id)->where('type', 'test.important')->count());
        // la bandeja del primero se entregó antes de que fallara su correo
        $this->assertSame(1, DB::table('notifications')->where('notifiable_id', $first->id)->count());
        $this->assertSame(1, ActivityLog::where('action', 'notification.failed')->count());
        $this->assertSame($first->id, ActivityLog::where('action', 'notification.failed')->sole()->target_user_id);
        Exceptions::assertReported(TransportException::class);
    }

    public function test_it_sends_once_per_person_and_never_to_the_excluded_one(): void
    {
        Notification::fake();
        [$a, $b, $actor] = User::factory()->count(3)->create()->all();

        $sent = app(Notifier::class)->send([$a, $b, $a, null, $actor], new DummyNotice, exceptUserId: $actor->id);

        $this->assertSame(2, $sent);
        Notification::assertSentToTimes($a, DummyNotice::class, 1);
        Notification::assertSentToTimes($b, DummyNotice::class, 1);
        Notification::assertNotSentTo($actor, DummyNotice::class);
    }
}
