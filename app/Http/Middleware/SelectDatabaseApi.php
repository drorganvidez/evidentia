<?php

namespace App\Http\Middleware;

use App\Models\Instance;
use Closure;
use Illuminate\Http\Request;

class SelectDatabaseApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {

        \Instantiation::set_default_connection();

        $param = $request->segment(3);
        $instance = Instance::where('route', $param)->first();

        if ($instance == null) {
            \Instantiation::set_default_instance();
        } else {
            \Instantiation::set($instance);
        }

        return $next($request);
    }
}
