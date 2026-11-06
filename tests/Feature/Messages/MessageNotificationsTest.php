<?php

namespace Tests\Feature\Messages;

use App\Enums\NotificationLevel;
use App\Models\Committee;
use App\Models\MessageThread;
use App\Models\User;
use App\Notifications\Catalog;
use App\Notifications\Events\MessageReceived;
use App\Notifications\Events\MessageReplied;
use App\Notifications\Events\MessageStatusChanged;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** The three notices of the threads reach who they must, once, and never whoever wrote. */
class MessageNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(E2eSeeder::class);
        Notification::fake();
    }

    private function person(string $who): User
    {
        return User::where('username', $who)->sole();
    }

    public function test_the_catalogue_knows_the_three_notices(): void
    {
        $this->assertContains(MessageReceived::class, Catalog::all());
        $this->assertSame(MessageReplied::class, Catalog::byKey('message.replied'));
        $this->assertSame(NotificationLevel::Informative, MessageStatusChanged::level());
        $this->assertSame([MessageStatusChanged::class], Catalog::triggeredBy('message.reopened'));
    }

    public function test_a_new_thread_reaches_its_audience(): void
    {
        $student = $this->person('estudiante');
        $logistics = Committee::where('name', 'Logística')->sole();

        $this->actingAs($student)->post(route('messages.store'), [
            'recipient' => 'COORDINATOR:'.$logistics->id, 'subject' => 'Fotos del montaje', 'body' => 'Las fotos están en la segunda prueba.',
        ])->assertRedirect();

        Notification::assertSentToTimes($this->person('coordinador'), MessageReceived::class, 1);
        Notification::assertNotSentTo($student, MessageReceived::class);
        Notification::assertNotSentTo($this->person('secretario'), MessageReceived::class);

        $thread = MessageThread::where('subject', 'Fotos del montaje')->sole();
        Notification::assertSentTo($this->person('coordinador'), MessageReceived::class, function (MessageReceived $notice, array $channels, User $user) use ($thread) {
            return $notice->url($user) === route('messages.show', $thread)
                && $notice->title($user) === 'Mensaje nuevo de Lucía Pérez Romero'
                && $notice->mailPanel($user) === 'Las fotos están en la segunda prueba.'
                && $notice->mailAction($user) === 'Abrir el mensaje';
        });
    }

    public function test_an_answer_reaches_the_participants_but_not_who_wrote_it(): void
    {
        $thread = MessageThread::where('subject', 'Cambio de turno en el registro')->sole();
        $coordinator = $this->person('coordinador');

        $this->actingAs($coordinator)->post(route('messages.reply', $thread), ['body' => 'Te cambio con Lucía.']);

        Notification::assertSentToTimes($this->person('companero'), MessageReplied::class, 1);
        Notification::assertNotSentTo($coordinator, MessageReplied::class);
        Notification::assertSentTo($this->person('companero'), MessageReplied::class, fn (MessageReplied $notice, array $channels, User $user) => $notice->mailPanel($user) === 'Te cambio con Lucía.'
            && $notice->mailSubject($user) === 'Respuesta · Cambio de turno en el registro');
    }

    public function test_resolving_and_reopening_reach_the_other_side(): void
    {
        $thread = MessageThread::where('subject', 'Cambio de turno en el registro')->sole();
        $coordinator = $this->person('coordinador');
        $classmate = $this->person('companero');

        $this->actingAs($coordinator)->post(route('messages.resolve', $thread));
        Notification::assertSentToTimes($classmate, MessageStatusChanged::class, 1);
        Notification::assertNotSentTo($coordinator, MessageStatusChanged::class);
        Notification::assertSentTo($classmate, MessageStatusChanged::class, fn (MessageStatusChanged $notice, array $channels, User $user) => $notice->title($user) === 'Hilo resuelto');

        $this->actingAs($classmate)->post(route('messages.reopen', $thread->fresh()));
        Notification::assertSentToTimes($coordinator, MessageStatusChanged::class, 1);
    }
}
