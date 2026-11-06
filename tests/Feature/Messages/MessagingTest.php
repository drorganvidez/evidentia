<?php

namespace Tests\Feature\Messages;

use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\MessageThread;
use App\Models\User;
use App\Services\Messaging\Audience;
use App\Services\Messaging\Messaging;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Opening, answering, resolving and reopening a thread, with the role of each writer and its annotations. */
class MessagingTest extends TestCase
{
    use RefreshDatabase;

    private Messaging $messaging;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(E2eSeeder::class);
        $this->messaging = app(Messaging::class);
    }

    private function person(string $who): User
    {
        return User::where('username', $who)->sole();
    }

    private function logistics(): Audience
    {
        return new Audience('COORDINATOR', Committee::where('name', 'Logística')->sole()->id);
    }

    public function test_opening_a_thread_writes_the_first_message_and_the_annotation(): void
    {
        $student = $this->person('estudiante');
        $evidence = Evidence::where('title', 'Recogida de material')->sole();
        $this->actingAs($student);

        $thread = $this->messaging->open($student, $this->logistics(), 'Fotos del montaje', 'Las fotos están en la segunda prueba.', $evidence);

        $this->assertSame(['STUDENT', 'COORDINATOR', 'open', 'evidence', $evidence->id, 'Recogida de material'], [
            $thread->author_role, $thread->recipient_role, $thread->state, $thread->about_type, (int) $thread->about_id, $thread->about_title,
        ]);
        $this->assertSame('Las fotos están en la segunda prueba.', $thread->messages()->sole()->body);
        $this->assertFalse($thread->isUnreadFor($student));
        $this->assertTrue($thread->isUnreadFor($this->person('coordinador')));

        $log = ActivityLog::where('action', 'message.sent')->latest('id')->first();
        $this->assertSame('STUDENT', $log->acting_role);
        $this->assertNull($log->committee_id);
        $this->assertSame('COORDINATOR:'.$thread->committee_id, $log->details['audience']);
        $this->assertSame([$this->person('coordinador')->id], $log->targets()->pluck('user_id')->map(fn ($id) => (int) $id)->all());
    }

    public function test_answering_updates_the_thread_and_reaches_the_other_participants(): void
    {
        $thread = MessageThread::where('subject', 'Cambio de turno en el registro')->sole();
        $coordinator = $this->person('coordinador');
        $this->actingAs($coordinator);
        $this->travel(1)->minutes();

        $message = $this->messaging->reply($thread, $coordinator, 'Te cambio con Lucía.');

        $thread->refresh();
        $this->assertSame('COORDINATOR', $message->role);
        $this->assertSame($coordinator->id, (int) $thread->last_message_by);
        $this->assertTrue($thread->isUnreadFor($this->person('companero')));
        $this->assertFalse($thread->isUnreadFor($coordinator));
        $log = ActivityLog::where('action', 'message.replied')->latest('id')->first();
        $this->assertSame('COORDINATOR', $log->acting_role);
        $this->assertContains($this->person('companero')->id, $log->targets()->pluck('user_id')->map(fn ($id) => (int) $id)->all());
        $this->assertNotContains($coordinator->id, $log->targets()->pluck('user_id')->map(fn ($id) => (int) $id)->all());
    }

    public function test_resolving_and_reopening_are_annotated_for_the_author(): void
    {
        $thread = MessageThread::where('subject', 'Cambio de turno en el registro')->sole();
        $coordinator = $this->person('coordinador');
        $this->actingAs($coordinator);

        $this->messaging->resolve($thread, $coordinator);
        $this->assertTrue($thread->fresh()->isResolved());
        $this->assertSame($coordinator->id, (int) $thread->fresh()->resolved_by);
        $resolved = ActivityLog::where('action', 'message.resolved')->sole();
        $this->assertSame($this->person('companero')->id, (int) $resolved->target_user_id);

        $this->actingAs($this->person('companero'));
        $this->messaging->reopen($thread->fresh(), $this->person('companero'));
        $this->assertSame('open', $thread->fresh()->state);
        $this->assertNull($thread->fresh()->resolved_at);
        $this->assertNull(ActivityLog::where('action', 'message.reopened')->sole()->target_user_id);
    }

    public function test_the_role_comes_from_the_audience_or_from_the_author(): void
    {
        $thread = MessageThread::where('subject', 'Propuesta para el cierre')->sole();

        $this->assertSame('LECTURE', Messaging::roleFor($this->person('profesor'), $thread, null));
        $this->assertSame('PRESIDENT', Messaging::roleFor($this->person('presidente'), $thread, null));
        $this->assertSame('STUDENT', Messaging::roleFor($this->person('companero'), $thread, null));
        $this->assertSame('COORDINATOR', Messaging::roleFor($this->person('coordinador'), MessageThread::where('subject', 'Cambio de turno en el registro')->sole(), null));
        // an author without the student role writes with their highest role
        $this->assertSame('PRESIDENT', Messaging::roleFor($this->person('presidente'), new Audience('LECTURE'), null));
        $this->assertSame('LECTURE', Messaging::roleFor($this->person('profesor'), $this->logistics(), null));
    }

    public function test_marking_as_read_is_not_annotated(): void
    {
        $thread = MessageThread::where('subject', 'Duda sobre el photocall')->sole();
        $student = $this->person('estudiante');
        $before = ActivityLog::count();

        $this->assertTrue($thread->isUnreadFor($student));
        $this->messaging->markRead($thread, $student);

        $this->assertFalse($thread->isUnreadFor($student));
        $this->assertSame($before, ActivityLog::count());
    }
}
