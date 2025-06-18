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
        
        $now = Carbon::now();
        $datetime = \Config::attendee_timestamp();

        if($now->gt($datetime)){
            return redirect()->route('home');
        }

        return $next($request);
    }
}
