<?php

namespace App\Http\Middleware;

use Closure;

class CheckNotNull
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $model)
    {
        $id = $request->route('id');
        if($id == null) // si se recibe por POST
        {
            $id = $request->_id;
        }

        $model = 'App\\Models\\' . $model;
        $entity = $model::where('id', $id)->first();

        if($entity == null){
            return redirect("/");
        }

        return $next($request);
    }
}
