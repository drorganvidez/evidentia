<?php

namespace App\Http\Middleware;

use App\Models\Evidence;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvidenceFromMyCommittee
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

        // los borradores no se han enviado al coordinador
        if ($evidence->committee->id != Auth::user()->coordinator->committee->id || $evidence->status == 'DRAFT') {
            abort(404);
        }

        return $next($request);
    }
}
