<?php

namespace App\Http\Controllers;

use Filepond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{

    private $filepond;

    public function __construct(Filepond $filepond)
    {
        $this->filepond = $filepond;
    }

    public function process(Request $request)
    {

        $user = Auth::user();
        $instance = \Instantiation::instance();
        $token = $request->session()->token();

        $files = $request->file('files');

        $file = $files[0];
        $path = Storage::putFileAs($instance.'/tmp/'.$user->username.'/'.$token.'/', $file, $file->getClientOriginalName());

        return Response::make($this->filepond->getServerIdFromPath($path), 200, [
            'Content-Type' => 'text/plain',
        ]);

    }

    public function delete(Request $request)
    {

        $path = $this->filepond->getPathFromServerId($request->getContent());
        Storage::delete($path);

        return Response::make('', 200, [
            'Content-Type' => 'text/plain',
        ]);


    }

    public function load($instance,$file_name)
    {

        $user = Auth::user();
        $token = session()->token();

        $route = $instance.'/tmp/'.$user->username.'/'.$token.'/'.$file_name;

        $response = Storage::download($route);

        // limpiar búfer de salida
        ob_end_clean();

        return $response;

    }

    public function remove($instance,$file_name)
    {
        $user = Auth::user();
        $instance = \Instantiation::instance();
        $token = session()->token();
        $tmp = $instance.'/tmp/'.$user->username.'/'.$token.'/';
        $path = $tmp.'/'.$file_name;

        Storage::delete($path);

        return Response::make('', 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
