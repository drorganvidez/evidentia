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

        $now = Carbon::now();
        $datetime = \App\Models\Configuration::first()->upload_evidences_timestamp;

        if($now->gt($datetime)){
            return redirect()->route('home');
        }

        return $next($request);
    }
}
