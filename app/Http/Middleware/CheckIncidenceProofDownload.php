<?php

namespace App\Http\Middleware;

use App\Models\IncidenceProof;
use App\Models\Proof;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckIncidenceProofDownload
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
        $instance = \Instantiation::instance();

        $id = $request->route('id');
        $proof = IncidenceProof::find($id);

        // 1. La prueba es mía
        if($proof->incidence->user->id == Auth::id()){
            return $next($request);
        }

        // 2. Soy coordinador y la prueba es de una evidencia dirigida a mi comité
        if(Auth::user()->hasRole('COORDINATOR')){
            $comittee = $proof->incidence->comittee;
            $my_comittee = Auth::user()->coordinator->comittee;
            if($comittee->id == $my_comittee->id){
                return $next($request);
            }
        }

        // 3. Soy profesor o presidente
        if(Auth::user()->hasRole('LECTURE') || Auth::user()->hasRole('PRESIDENT')){
            return $next($request);
        }

        return redirect()->route('home',$instance);
    }
}
