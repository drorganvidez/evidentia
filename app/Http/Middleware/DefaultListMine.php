<?php

namespace App\Http\Middleware;

use App\Models\DefaultList;
use Closure;
use Illuminate\Support\Facades\Auth;

class DefaultListMine
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $id = $request->route('id');
        if ($id == null) { // si se recibe por POST
            $id = $request->_id;
        }
        $defaultlist = DefaultList::findOrFail($id);
        $secretary = Auth::user()->secretary;

        if ($secretary == null || $defaultlist->secretary_id != $secretary->id) {
            abort(404);
        }

        return $next($request);
    }
}
