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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $instance = \Instantiation::instance();

        $id = $request->route('id');
        if($id == null) // si se recibe por POST
        {
            $id = $request->_id;

            if($id == null)
            {
                $id = $request->input('meeting_minutes_id');
            }
        }

        $meeting_minutes = MeetingMinutes::findOrFail($id);

        if($meeting_minutes->secretary_id != Auth::user()->secretary->id)
        {
            abort('404');
        }
        return $next($request);
    }
}
