<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class CheckRegisterMeeting
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $now = Carbon::now();
        $datetime = \Config::meetings_timestamp();

        if ($now->gt($datetime)) {
            abort(404);
        }

        return $next($request);
    }
}
