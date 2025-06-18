<?php

namespace App\Http\Controllers;

use App\Models\Proof;
use Illuminate\Support\Facades\Storage;

class ProofController extends Controller
{
    public function download($id)
    {

        $proof = Proof::find($id);
        $file = $proof->file;

        $response = Storage::download($file->route);

        // limpiar búfer de salida
        if (ob_get_level()) {
            ob_end_clean();
        }

        return $response;
    }
}
