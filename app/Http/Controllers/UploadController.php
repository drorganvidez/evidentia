<?php

namespace App\Http\Controllers;

use Filepond;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        $token = $request->session()->token();

        $files = $request->file('files');
        $file = is_array($files) ? $files[0] : $files;

        if (! $file || ! $file->isValid()) {
            \Log::error('Archivo inválido o no recibido correctamente.');

            return response('Archivo inválido', 400);
        }

        if (! $this->hasValidContent($file)) {
            return response('El contenido del archivo no corresponde a su formato.', 422)
                ->header('Content-Type', 'text/plain');
        }

        $relativePath = 'tmp/'.$user->username.'/'.$token.'/';
        $path = Storage::putFileAs($relativePath, $file, $file->getClientOriginalName());

        \Log::info('Archivo subido correctamente a: '.$path);

        return response($this->filepond->getServerIdFromPath($path), 200)
            ->header('Content-Type', 'text/plain');
    }

    private function hasValidContent(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['psd', 'xcf'], true)) {
            return true;
        }

        $path = $file->getRealPath();

        if ($path === false) {
            return false;
        }

        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        $header = fread($handle, 16);
        fclose($handle);

        if (! is_string($header)) {
            return false;
        }

        return match ($extension) {
            // Adobe Photoshop (PSD): "8BPS", version 1 (0x0001) and 6 reserved zero bytes
            'psd' => str_starts_with($header, "8BPS\x00\x01\x00\x00\x00\x00\x00\x00"),
            // GIMP (XCF): "gimp xcf " followed by "file" or "v001".."v999" and a NUL byte
            'xcf' => preg_match('/\Agimp xcf (?:file|v\d{3})\x00/', $header) === 1,
        };
    }

    public function delete(Request $request)
    {

        $path = $this->filepond->getPathFromServerId($request->getContent());
        Storage::delete($path);

        return Response::make('', 200, [
            'Content-Type' => 'text/plain',
        ]);

    }

    public function load($file_name)
    {

        $user = Auth::user();
        $token = session()->token();

        $route = '/tmp/'.$user->username.'/'.$token.'/'.$file_name;

        if (! Storage::exists($route)) {
            abort(404);
        }

        $response = Storage::download($route);

        // limpiar búfer de salida
        if (ob_get_level()) {
            ob_end_clean();
        }

        return $response;

    }

    public function remove($file_name)
    {
        $user = Auth::user();

        $token = session()->token();
        $tmp = '/tmp/'.$user->username.'/'.$token.'/';
        $path = $tmp.'/'.$file_name;

        Storage::delete($path);

        return Response::make('', 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
