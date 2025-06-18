<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Image;

class AvatarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function avatar($id)
    {
        $user = User::find($id);

        $path = null;
        // ¿el usuario tiene avatar?
        if ($user->avatar != null) {
            $path = storage_path('app/'.$user->avatar->file->route);
        } else {
            $path = storage_path('app/public/default.png');
        }

        if (! File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header('Content-Type', $type);

        // recorte de imagen
        [$width, $height, $type, $attr] = getimagesize($path);

        $img = null;

        // ¿La imagen es más alta que ancha?
        if ($height > $width) {
            $new_width = $width;
            $new_height = $width;
            $img = Image::make($file)->crop($new_width, $new_height);
        }

        // ¿La imagen es más ancha que alta?
        if ($height < $width) {
            $new_width = $height;
            $new_height = $height;
            $img = Image::make($file)->crop($new_width, $new_height);
        }

        // ¿La imagen es cuadrada?
        if ($height == $width) {
            $img = Image::make($file)->crop($width, $height);
        }

        // limpiar búfer de salida
        if (ob_get_level()) {
            ob_end_clean();
        }

        return $img->response('jpg');

        return $response;
    }
}
