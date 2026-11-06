<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Evidence;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * The access page with the figures of the edition: the hours and the evidences accepted and the students.
     */
    public function showLoginForm(): View
    {
        $accepted = Evidence::where('last', true)->where('status', 'ACCEPTED');
        $students = Role::where('rol', 'STUDENT')->first();

        return view('auth.login', [
            'edition' => now()->year,
            'figures' => [
                'hours' => (float) (clone $accepted)->sum('hours'),
                'evidences' => (clone $accepted)->count(),
                'students' => $students?->users()->count() ?? 0,
            ],
        ]);
    }
}
