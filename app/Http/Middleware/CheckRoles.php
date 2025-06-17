<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRoles
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        foreach ($user->roles as $rol) {
            if (in_array($rol->rol, $roles)) {
                return $next($request);
            }
        }
        return redirect()->route('home');
    }
}
