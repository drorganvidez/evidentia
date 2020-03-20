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

        $admin_routes = array("", "admin", "login", "logout");

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

        Config::set('database.connections.instance.host', $instance->host);
        Config::set('database.connections.instance.port', $instance->port);
        Config::set('database.connections.instance.username', $instance->username);
        Config::set('database.connections.instance.password', $instance->password);
        Config::set('database.connections.instance.database', $instance->database);
        Config::set('database.default', 'instance');

        return $next($request);

    }
}
