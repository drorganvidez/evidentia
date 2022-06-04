<?php

namespace App\Http\Middleware;

use App\Models\Instance;
use App\Models\SignatureSheet;
use Closure;
use Illuminate\Support\Facades\Config;

class SelectDatabaseApi
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

        \Instantiation::set_default_connection();

        $param = $request->segment(2);
        $instance = Instance::where('route', $param)->first();

        if ($instance == null) {
            \Instantiation::set_default_instance();
        } else {
            \Instantiation::set($instance);
        }

        return $next($request);
    }
}