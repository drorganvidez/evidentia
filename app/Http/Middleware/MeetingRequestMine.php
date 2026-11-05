<?php

namespace App\Http\Middleware;

use App\Models\MeetingRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingRequestMine
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $id = $request->route('id') ?? $request->input('_id');

        $meeting_request = MeetingRequest::findOrFail($id);

        $secretary = Auth::user()->secretary;

        if ($secretary == null or $meeting_request->secretary_id != $secretary->id) {
            abort(404);
        }

        return $next($request);
    }
}
