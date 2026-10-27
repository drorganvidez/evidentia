<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckNotNull
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next, $model)
    {
        $id = $request->route('id');
        if ($id == null) { // si se recibe por POST
            $id = $request->_id;
        }

        $model = 'App\\Models\\'.$model;
        $entity = $model::where('id', $id)->first();

        if ($entity == null) {
            abort(404);
        }

        return $next($request);
    }
}
