<?php

namespace App\Http\Middleware;

use App\Models\Evidence;
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

        $id = $request->route('id');
        if($id == null) // if it's a POST request
        {
            $id = $request->_id;
        }
        $evidence = Evidence::findOrFail($id);

        if($evidence->user->id != Auth::id())
        {
            return abort('404');
        }

        return $next($request);
    }
}
