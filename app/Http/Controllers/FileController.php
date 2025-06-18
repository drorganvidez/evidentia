<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function remove(Request $request)
    {
        $id = $request->_id;
        $file = File::find($id);
        Storage::delete($file->route);
        $file->delete();
    }
}
