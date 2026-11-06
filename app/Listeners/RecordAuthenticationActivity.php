<?php

namespace App\Listeners;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

/**
 * Sign-ins, failed sign-ins, sign-outs and password resets. The public signature signs in and out to check the
 * credentials, so only its failures are kept. The credentials of a failed sign-in carry the password and never reach
 * the log. Discovered, never registered by hand.
 */
final class RecordAuthenticationActivity
{
    public function handleLogin(Login $event): void
    {
        if (request()->routeIs('sign_p') || ! $event->user instanceof User) {
            return;
        }

        Activity::record(
            ActivityAction::Login,
            $event->user,
            details: [
                'remember' => $event->remember,
                // POST /login has no name; the remember cookie signs in on any other route
                'via' => request()->isMethod('POST') && request()->route()?->getName() === null ? 'form' : 'cookie',
                'blocked' => (bool) $event->user->block,
            ],
            deadline: false,
            actor: $event->user,
        );
    }

    public function handleFailed(Failed $event): void
    {
        Activity::system(
            ActivityAction::LoginFailed,
            target: $event->user instanceof User ? $event->user : null,
            details: [
                'context' => request()->routeIs('sign_p') ? 'sign' : 'login',
                'known' => $event->user !== null,
            ],
            committeeId: false,
        );
    }

    public function handleLockout(Lockout $event): void
    {
        Activity::system(
            ActivityAction::LoginFailed,
            target: User::where('email', (string) $event->request->input('email'))->first(),
            details: ['context' => 'login', 'lockout' => true],
            committeeId: false,
        );
    }

    public function handleLogout(Logout $event): void
    {
        if (request()->routeIs('sign_p') || ! $event->user instanceof User) {
            return;
        }

        Activity::record(ActivityAction::Logout, $event->user, deadline: false, actor: $event->user);
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        Activity::record(ActivityAction::PasswordReset, $event->user, deadline: false, actor: $event->user);
    }
}
