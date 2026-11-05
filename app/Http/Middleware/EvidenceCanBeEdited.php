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

        $evidence = Evidence::findOrFail($request->route('id') ?? $request->input('_id'));

        // se decide por la última versión: una versión antigua no puede reabrir una evidencia ya enviada
        $status = $evidence->findHeaderEvidence()->status;

        if ($status != 'DRAFT' && $status != 'REJECTED') {
            abort(404);
        }

        return $next($request);
    }
}
