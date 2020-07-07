<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckBlock
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
        if(Auth::check() && Auth::user()->block){
            return redirect()->route('block',$instance);
        }

        return $next($request);

    }
}
