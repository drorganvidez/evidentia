<?php

namespace App\Http\Middleware;

use App\Models\SignatureSheet;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignatureSheetMine
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $id = $request->route('id') ?? $request->input('_id');

        $signature_sheet = SignatureSheet::findOrFail($id);

        $secretary = Auth::user()->secretary;

        if ($secretary == null or $signature_sheet->secretary_id != $secretary->id) {
            abort(404);
        }

        return $next($request);
    }
}
