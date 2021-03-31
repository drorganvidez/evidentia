<?php

namespace App\Http\Middleware;

use App\Models\Evidence;
use Closure;

class EvidenceCanBeEdited
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
        $evidence = Evidence::find($id);

        if($evidence->status != "DRAFT" and $evidence->status != "REJECTED")
        {
            return redirect()->route('home',$instance);
        }

        return $next($request);
    }
}
