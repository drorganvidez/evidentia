<?php

namespace Tests\Feature\Notifications;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\User;
use App\Notifications\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Notifications\Fixtures\DummyInformativeNotice;
use Tests\Feature\Notifications\Fixtures\DummyNotice;
use Tests\Feature\Notifications\Fixtures\FlakyTransport;
use Tests\TestCase;

/**
 * Every notice really sent leaves one notification.sent with its channels, and every failed channel one
 * notification.failed.
 */
class RecordNotificationActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private User $author;

    private Evidence $evidence;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->actor, $this->author] = User::factory()->count(2)->create()->all();
        $committee = Committee::create(['name' => 'Logística']);
        $this->evidence = Evidence::create(['title' => 'Organización de mesas', 'status' => 'PENDING', 'user_id' => $this->author->id, 'committee_id' => $committee->id]);
    }

    protected function tearDown(): void
    {
        Catalog::flush();
        parent::tearDown();
    }

    public function test_one_sent_row_per_notice_with_its_channels(): void
    {
        Catalog::using([DummyNotice::class]);

        $accepted = Activity::record(ActivityAction::EvidenceAccepted, $this->evidence, $this->author, actor: $this->actor);

        $sent = ActivityLog::where('action', 'notification.sent')->sole();
        $notification = DB::table('notifications')->sole();
        $this->assertNull($sent->user_id);
        $this->assertSame($this->author->id, $sent->target_user_id);
        $this->assertSame('evidence', $sent->subject_type);
        $this->assertSame($this->evidence->id, $sent->subject_id);
        $this->assertSame('Organización de mesas', $sent->subject_title);
        $this->assertNull($sent->committee_id);
        $this->assertEquals([
            'type' => 'test.important', 'level' => 'important', 'title' => 'Evidencia aceptada', 'channels' => ['database', 'mail'],
            'notification_id' => $notification->id, 'activity_id' => $accepted->id,
        ], $sent->details);
        $this->assertSame($accepted->request_id, $sent->request_id);
        $this->assertSame('test.important', $notification->type);
        $this->assertSame('user', $notification->notifiable_type);
        $data = json_decode($notification->data, true);
        $this->assertSame('/?from=aviso', $data['url']);
        $this->assertSame($accepted->id, $data['activity_id']);
        $this->assertSame($this->evidence->id, $data['subject_root_id']);
        $this->assertCount(1, Mail::mailer()->getSymfonyTransport()->messages());
    }

    public function test_with_the_mail_off_the_channels_are_only_the_inbox(): void
    {
        Catalog::using([DummyInformativeNotice::class]);

        Activity::record(ActivityAction::EvidenceAccepted, $this->evidence, $this->author, actor: $this->actor);

        $this->assertSame(['database'], ActivityLog::where('action', 'notification.sent')->sole()->details['channels']);
        $this->assertCount(0, Mail::mailer()->getSymfonyTransport()->messages());
    }

    public function test_a_failed_mail_leaves_a_single_failed_row(): void
    {
        Exceptions::fake();
        Catalog::using([DummyNotice::class]);
        $this->author->update(['email' => 'caido@example.com']);
        Mail::extend('flaky', fn () => new FlakyTransport('caido@example.com'));
        config(['mail.mailers.flaky' => ['transport' => 'flaky'], 'mail.default' => 'flaky']);

        Activity::record(ActivityAction::EvidenceAccepted, $this->evidence, $this->author, actor: $this->actor);

        $failed = ActivityLog::where('action', 'notification.failed')->sole();
        $this->assertSame($this->author->id, $failed->target_user_id);
        $this->assertSame('mail', $failed->details['channel']);
        $this->assertSame('test.important', $failed->details['type']);
        $this->assertSame('Servidor de correo caído', $failed->details['error']);
        // la bandeja, primer canal, sí llegó
        $this->assertSame(1, ActivityLog::where('action', 'notification.sent')->count());
    }
}
