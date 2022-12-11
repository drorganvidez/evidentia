<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\IncidenceProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IncidenceProofController extends Controller
{

    public function download($instance,$id){

        $proof = IncidenceProof::find($id);
        $file = $proof->file;

        $response = Storage::download($file->route);

        // limpiar búfer de salida
        ob_end_clean();

        return $response;
    }

}
