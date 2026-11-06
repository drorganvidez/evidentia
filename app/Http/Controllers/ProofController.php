<?php

namespace App\Http\Controllers;

use App\Models\Proof;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * Shows an image or a PDF in the browser, with the type its extension allows and a policy that runs nothing of it;
     * any other file is downloaded.
     */
    public function view($id): Response
    {
        $file = Proof::find($id)->file;
        $mime = $file->previewMime();

        if ($mime === null) {
            return redirect()->route('proof.download', ['id' => $id]);
        }

        $response = Storage::response($file->route, $file->name.'.'.strtolower($file->type), [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
            // An image is drawn with nothing else allowed; the PDF viewer of Chrome refuses a sandboxed document
            'Content-Security-Policy' => $mime === 'application/pdf'
                ? "default-src 'none'; object-src 'self'; frame-ancestors 'none'"
                : "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; frame-ancestors 'none'; sandbox",
        ]);

        return $response;
    }
}
