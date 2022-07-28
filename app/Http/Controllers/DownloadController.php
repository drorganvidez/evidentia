<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function download_file($instance, $file_id)
    {

        $file = File::findOrFail($file_id);

        $response = Storage::download($file->route);

        // limpiar búfer de salida
        ob_end_clean();

        return $response;

    }
}
