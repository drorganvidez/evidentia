<?php

namespace App\Http\Middleware;

use App\Models\Instance;
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

        $admin_routes = array("", "admin","logout", "deploy");

        // si es una de las rutas permitidas en el array, dejo pasar
        if (in_array($param, $admin_routes))
        {
            return $next($request);
        }

        // si no es ninguna ruta, compruebo que sea una instancia definida en la BBDD
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
