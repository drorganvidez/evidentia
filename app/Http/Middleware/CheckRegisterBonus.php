<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class CheckRegisterBonus
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
        $datetime = \Config::bonus_timestamp();

        if ($now->gt($datetime)) {
            abort(404);
        }

        return $next($request);
    }
}
