<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Incidence;
class CheckIncidenceIsNotInReview
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

        $id = $request->_id;
        $incidence = Incidence::find($id);

        if($incidence->status == "CLOSED")
        {
            return redirect()->route('incidence.list',$instance);
        }
        if($incidence->status == "INREVIEW")
        {
            return redirect()->route('incidence.list',$instance);
        }


        return $next($request);
    }
}
