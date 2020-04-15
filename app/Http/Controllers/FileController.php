<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function download($instance,$id){

        $file = File::find($id);

        $response = Storage::download($file->route);

        // limpiar búfer de salida
        ob_end_clean();

        return $response;
    }

    public function remove(Request $request)
    {
        $id = $request->_id;
        $file = File::find($id);
        Storage::delete($file->route);
        $file->delete();
    }


}
