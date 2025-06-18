<?php

namespace App\Http\Middleware;

use App\Models\Proof;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckProofDownload
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
        /**
         *  Puedo descargar una prueba en tres supuestos:
         *      1. La prueba es mía
         *      2. Soy coordinador y la prueba es de una evidencia dirigida a mi comité
         *      3. Soy profesor o presidente
         */
        

        $id = $request->route('id');
        $proof = Proof::find($id);

        // 1. La prueba es mía
        if($proof->evidence->user->id == Auth::id()){
            return $next($request);
        }

        // 2. Soy coordinador y la prueba es de una evidencia dirigida a mi comité
        if(Auth::user()->hasRole('COORDINATOR')){
            $committee = $proof->evidence->committee;
            $my_committee = Auth::user()->coordinator->committee;
            if($committee->id == $my_committee->id){
                return $next($request);
            }
        }

        // 3. Soy profesor o presidente
        if(Auth::user()->hasRole('LECTURE') || Auth::user()->hasRole('PRESIDENT')){
            return $next($request);
        }

        abort(404);
    }
}
