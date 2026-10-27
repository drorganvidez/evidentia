<?php

namespace App\Http\Middleware;

use App\Models\Evidence;
use Closure;
use Illuminate\Http\Request;

class EvidenceCanBeEdited
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
        $evidence = Evidence::find($id);

        if ($evidence->status != 'DRAFT' and $evidence->status != 'REJECTED') {
            abort(404);
        }

        return $next($request);
    }
}
