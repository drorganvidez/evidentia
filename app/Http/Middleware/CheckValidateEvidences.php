<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;

class CheckValidateEvidences
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
        $datetime = \Config::validate_evidences_timestamp();

        if($now->gt($datetime)){
            return redirect()->route('home');
        }

        return $next($request);
    }
}
