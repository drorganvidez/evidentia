<?php

namespace App\Http\Middleware;

use App\Models\Incidence;
use Closure;
use Illuminate\Support\Facades\Auth;

class IncidenceMine
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

        $id = $request->route('id');
        if($id == null) // si se recibe por POST
        {
            $id = $request->_id;
        }
        $incidence = Incidence::find($id);

        if($incidence->user->id != Auth::id())
        {
            return redirect()->route('home',$instance);
        }

        return $next($request);
    }
}