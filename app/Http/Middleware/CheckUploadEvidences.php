<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;

class CheckUploadEvidences
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $now = Carbon::now();
        $datetime = \Config::upload_evidences_timestamp();

        if ($now->gt($datetime)) {
            abort(404);
        }

        return $next($request);
    }
}
