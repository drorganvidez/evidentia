<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;

class CheckRegisterEventsAndAttendings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $instance = \Instantiation::instance();
        $now = Carbon::now();
        $datetime = \Config::attendee_timestamp();

        if($now->gt($datetime)){
            return abort('404');
        }

        return $next($request);
    }
}
