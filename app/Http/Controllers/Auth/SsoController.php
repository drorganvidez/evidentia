<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\UsSsoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoController extends Controller
{
    public function __construct(private UsSsoService $sso)
    {
        $this->middleware('guest');
    }

    /**
     * Redirects the user to the University of Seville SSO login page, or to the local mock page in development.
     */
    public function redirect(): RedirectResponse
    {
        abort_unless($this->sso->isEnabled(), 404);

        if ($this->sso->isMockEnabled()) {
            return redirect()->route('sso.mock');
        }

        return redirect()->away($this->sso->loginUrl(route('sso.callback')));
    }

    /**
     * Renders the local development screen to simulate a University of Seville SSO response with any UVUS.
     */
    public function mock(): View
    {
        abort_unless($this->sso->isMockEnabled(), 404);

        return view('auth.sso-mock');
    }

    /**
     * Handles the callback from the University of Seville SSO (or the local mock), matches by UVUS and signs in.
     */
    public function callback(Request $request): RedirectResponse
    {
        abort_unless($this->sso->isEnabled(), 404);

        if ($this->sso->isMockEnabled() && $request->has('uvus')) {
            $uvus = $this->sso->normalizeUvus((string) $request->query('uvus', ''));
        } else {
            $ticket = (string) $request->query('ticket', '');
            $uvus = $this->sso->validateTicket($ticket, route('sso.callback'));
        }

        if ($uvus === null) {
            Activity::system(
                ActivityAction::LoginFailed,
                target: null,
                details: [
                    'context' => 'login',
                    'known' => false,
                ],
                committeeId: false,
            );

            return redirect()->route('login')->withErrors([
                'sso' => 'No se ha podido verificar tu identidad con la cuenta de la Universidad de Sevilla.',
            ]);
        }

        $user = User::where('username', $uvus)
            ->orWhereRaw('LOWER(username) = ?', [$uvus])
            ->first();

        if ($user === null) {
            Activity::system(
                ActivityAction::LoginFailed,
                target: null,
                details: [
                    'context' => 'login',
                    'known' => false,
                ],
                committeeId: false,
            );

            return redirect()->route('login')->withErrors([
                'sso' => "Tu cuenta UVUS («{$uvus}») no está importada o autorizada en Evidentia.",
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}

