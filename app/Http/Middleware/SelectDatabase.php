<?php

namespace App\Http\Middleware;

use App\Instance;
use Closure;
use Illuminate\Support\Facades\Config;

class SelectDatabase
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
        $param = $request->segment(1);

        $admin_routes = array("", "administration", "admin", "login", "logout", "deploy");

        if (in_array($param, $admin_routes))
        {
            return $next($request);
        }

        $instance = Instance::where('route',$param)->first();

        // if database doesn't exist
        if($instance == null)
        {
            return redirect('/');
        }

        \Instantiation::set($instance);

        return $next($request);

    }
}
