<?php

namespace App\Http\Middleware;

use App\Models\Bonus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BonusMine
{
    /**
     * Only lets the secretary of the bonus' committee reach it.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $bonus = Bonus::findOrFail($request->route('id') ?? $request->input('_id'));
        $secretary = Auth::user()->secretary;

        if ($secretary == null || $bonus->committee_id != $secretary->committee_id) {
            abort(404);
        }

        return $next($request);
    }
}
