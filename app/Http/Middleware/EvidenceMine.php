<?php

namespace App\Http\Middleware;

use App\Models\Evidence;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvidenceMine
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $id = $request->route('id');
        if ($id == null) { // si se recibe por POST
            $id = $request->_id;
        }
        $evidence = Evidence::find($id);

        // a coauthor reads the evidence once it is sent, without touching it
        $reader = $request->routeIs('evidence.view') && $evidence->status !== 'DRAFT' && $evidence->hasCoauthor(Auth::user());

        if ($evidence->user->id != Auth::id() && ! $reader) {
            abort(404);
        }

        return $next($request);
    }
}
