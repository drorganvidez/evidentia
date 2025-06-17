<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {

        $param = $request->segment(1);

        if (! $request->expectsJson()) {

            if($param == "admin"){
                return route('instances.home');
            }

            return $param.'/login';
        }
    }
}
