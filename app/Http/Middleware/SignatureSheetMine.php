<?php

namespace App\Http\Middleware;

use App\Models\MeetingRequest;
use App\Models\SignatureSheet;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignatureSheetMine
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        

        $id = $request->route('id');
        if($id == null) // si se recibe por POST
        {
            $id = $request->_id;

            if($id == null)
            {
                $id = $request->input('signature_sheet_id');
            }
        }

        $signature_sheet = SignatureSheet::findOrFail($id);

        if($signature_sheet->secretary_id != Auth::user()->secretary->id)
        {
            abort('404');
        }
        return $next($request);
    }
}
