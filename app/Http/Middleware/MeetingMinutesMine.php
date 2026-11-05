<?php

namespace App\Http\Middleware;

use App\Models\MeetingMinutes;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingMinutesMine
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $id = $request->route('id') ?? $request->input('_id');

        $meeting_minutes = MeetingMinutes::findOrFail($id);

        $secretary = Auth::user()->secretary;

        if ($secretary == null || $meeting_minutes->secretary_id != $secretary->id) {
            abort(404);
        }

        return $next($request);
    }
}
