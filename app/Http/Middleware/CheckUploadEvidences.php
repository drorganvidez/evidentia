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
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $instance = \Instantiation::instance();
        $now = Carbon::now();
        $datetime = \Config::upload_evidences_timestamp();

        if($now->gt($datetime)){
            return redirect()->route('home',$instance);
        }

        return $next($request);
    }
}
