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

        $id = $request->route('id');
        if ($id == null) { // si se recibe por POST
            $id = $request->_id;

            if ($id == null) {
                $id = $request->input('meeting_request_id');
            }
        }

        $meeting_request = MeetingRequest::findOrFail($id);

        if ($meeting_request->secretary_id != Auth::user()->secretary->id) {
            abort('404');
        }

        return $next($request);
    }
}
