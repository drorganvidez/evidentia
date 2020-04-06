<?php

namespace App\Http\Middleware;

use App\Evidence;
use Closure;
use Illuminate\Support\Facades\Auth;

class EvidenceMine
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

        if($evidence->user->id != Auth::id())
        {
            return redirect()->route('home',$instance);
        }

        return $next($request);
    }
}
