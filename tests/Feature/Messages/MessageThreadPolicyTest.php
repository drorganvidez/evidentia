<?php

namespace Tests\Feature\Messages;

use App\Models\MessageThread;
use App\Models\User;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Who sees, answers, resolves and reopens a thread. */
class MessageThreadPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_matrix_of_a_thread_to_a_coordination(): void
    {
        $this->seed(E2eSeeder::class);
        $thread = MessageThread::where('subject', 'Cambio de turno en el registro')->sole();
        $can = fn (string $who, string $ability, MessageThread $thread) => User::where('username', $who)->sole()->can($ability, $thread);

        // open → author, audience and lecturers view and answer; only the audience and the lecturers resolve
        $expected = [
            'companero' => ['view' => true, 'reply' => true, 'resolve' => false, 'reopen' => false],
            'coordinador' => ['view' => true, 'reply' => true, 'resolve' => true, 'reopen' => false],
            'profesor' => ['view' => true, 'reply' => true, 'resolve' => true, 'reopen' => false],
            'estudiante' => ['view' => false, 'reply' => false, 'resolve' => false, 'reopen' => false],
            'secretario' => ['view' => false, 'reply' => false, 'resolve' => false, 'reopen' => false],
            'presidente' => ['view' => false, 'reply' => false, 'resolve' => false, 'reopen' => false],
        ];
        foreach ($expected as $who => $abilities) {
            foreach ($abilities as $ability => $allowed) {
                $this->assertSame($allowed, $can($who, $ability, $thread), "$who $ability abierto");
            }
        }

        // resolved → nobody answers; author, audience and lecturers reopen
        $thread->update(['state' => 'resolved']);
        foreach (['companero' => true, 'coordinador' => true, 'profesor' => true, 'estudiante' => false] as $who => $allowed) {
            $this->assertFalse($can($who, 'reply', $thread), "$who responde resuelto");
            $this->assertFalse($can($who, 'resolve', $thread), "$who resuelve resuelto");
            $this->assertSame($allowed, $can($who, 'reopen', $thread), "$who reabre");
        }

        // the presidency includes the lecturers
        $president = MessageThread::where('subject', 'Propuesta para el cierre')->sole();
        $this->assertTrue($can('presidente', 'resolve', $president));
        $this->assertTrue($can('profesor', 'resolve', $president));
        $this->assertFalse($can('coordinador', 'view', $president));
    }
}
