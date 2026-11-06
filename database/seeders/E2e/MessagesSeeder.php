<?php

namespace Database\Seeders\E2e;

use App\Facades\Activity;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\User;
use App\Services\Messaging\Audience;
use App\Services\Messaging\Messaging;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Threads of the end-to-end seed: the student asks the coordination of Logística about an evidence and the
 * coordinator answers (unread for the student), a classmate writes to the coordination (unread for the coordinator)
 * and to the presidency (out of the coordinator's reach).
 */
class MessagesSeeder extends Seeder
{
    public function run(Messaging $messaging): void
    {
        $who = fn (string $username): User => User::where('username', $username)->sole();
        $logistics = Committee::where('name', 'Logística')->sole()->id;
        $as = function (User $user, callable $action): mixed {
            Activity::startRequest();
            Auth::setUser($user);
            $result = $action();
            Auth::forgetUser();

            return $result;
        };

        // The question an hour ago, the answer now: the student has not read it yet
        $now = Carbon::getTestNow();
        Carbon::setTestNow(now()->subHour());
        $student = $who('estudiante');
        $thread = $as($student, fn () => $messaging->open(
            $student,
            new Audience('COORDINATOR', $logistics),
            'Duda sobre el photocall',
            '¿Cuento también el desmontaje del photocall o solo el montaje?',
            Evidence::where('title', 'Montaje del photocall')->sole(),
        ));
        Carbon::setTestNow($now);
        $coordinator = $who('coordinador');
        $as($coordinator, fn () => $messaging->reply($thread->fresh(), $coordinator, 'Cuenta las dos cosas en la misma evidencia.'));

        $classmate = $who('companero');
        $as($classmate, fn () => $messaging->open(
            $classmate,
            new Audience('COORDINATOR', $logistics),
            'Cambio de turno en el registro',
            'Mañana no puedo cubrir el turno de tarde del registro.',
            null,
        ));
        $as($classmate, fn () => $messaging->open(
            $classmate,
            new Audience('PRESIDENT'),
            'Propuesta para el cierre',
            'Propongo una foto de grupo en el cierre de las jornadas.',
            null,
        ));

        Activity::startRequest();
    }
}
