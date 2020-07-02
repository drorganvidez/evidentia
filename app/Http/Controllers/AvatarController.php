<?php

namespace App\Http\Controllers;


use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Image;

class AvatarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function avatar($instance,$id)
    {
        $instance = \Instantiation::instance();
        $user = User::find($id);

        $path = null;
        // ¿el usuario tiene avatar?
        if($user->avatar != null) {
            $path = storage_path('app/' . $user->avatar->file->route);
        }else{
            $path = storage_path('app/public/default.png');
        }

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        // limpiar búfer de salida
        ob_end_clean();

        $img = Image::make($file)->crop(500, 500);

        return $img->response('jpg');

        return $response;
    }
}
