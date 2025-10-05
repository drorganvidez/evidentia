<?php

namespace App\Http\Controllers;

use App\Models\SignatureSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignController extends Controller
{
    public function sign($random_identifier)
    {
        $signature_sheet = SignatureSheet::where('random_identifier', $random_identifier)->first();

        if ($signature_sheet == null) {

            abort(404);
        }

        return view('sign.sign', ['signature_sheet' => $signature_sheet]);

    }

    public function sign_p(Request $request)
    {

        $signature_sheet = SignatureSheet::findOrFail($request->input('signature_sheet'));
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            // compruebo que el usuario no es un secretario e intenta firmar en su propia hoja
            if (Auth::user()->secretary != null) {
                if (Auth::user()->secretary->id == $signature_sheet->secretary->id) {
                    Auth::logout();
                    $sign_message = 'Eres el secretario de esta reunión, no es necesario que firmes en tu propia hoja de firmas.';

                    return redirect()->route('sign.finish', [])->with('sign_message', $sign_message);
                }
            }

            // comprobamos que no haya firmado con anterioridad
            if (! Auth::user()->signatureSheets->contains($signature_sheet->id)) {

                // firma
                Auth::user()->signatureSheets()->attach($signature_sheet->id);

                Auth::logout();

                $sign_message = 'Tu firma ha sido validada y registrada correctamente.';

                return redirect()->route('sign.finish')->with('sign_message', $sign_message);

            }

            Auth::logout();

            $sign_message = 'Parece ser que ya has firmado en esta reunión. Si consideras que es un error, contacta con el secretario de tu comité.';

            return redirect()->route('sign.finish', [])->with('sign_message', $sign_message);

        }

        return back()->withInput()->with('error', 'Las credenciales no son válidas.');
    }

    public function finish()
    {

        if (session('sign_message')) {
            return view('sign.sign_finish', []);
        }

        return redirect()->route('home');

    }
}
