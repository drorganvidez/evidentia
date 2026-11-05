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
        $evidence = Evidence::findOrFail($id);
        $coordinator = Auth::user()->coordinator;

        // solo el coordinador del comité, y los borradores no se le han enviado
        if ($coordinator == null || $evidence->committee_id != $coordinator->committee_id || $evidence->status == 'DRAFT') {
            abort(404);
        }

        return $next($request);
    }
}
