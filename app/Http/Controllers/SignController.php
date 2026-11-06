<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Enums\ActivityFlag;
use App\Facades\Activity;
use App\Models\SignatureSheet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

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

        // la hoja se identifica por el código de su enlace, no por su id secuencial
        $signature_sheet = SignatureSheet::where('random_identifier', (string) $request->input('signature_sheet'))->firstOrFail();
        $credentials = $request->only('email', 'password') + ['block' => false];

        // como en el login: 5 intentos por minuto para cada email y dirección
        $throttle_key = 'sign|'.Str::lower((string) $request->input('email')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttle_key, 5)) {
            Activity::system(
                ActivityAction::LoginFailed,
                target: User::where('email', (string) $request->input('email'))->first(),
                details: ['context' => 'sign', 'lockout' => true],
            );

            return back()->withInput()->with('error', 'Demasiados intentos. Vuelve a intentarlo en '.RateLimiter::availableIn($throttle_key).' segundos.');
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($throttle_key);

            // compruebo que el usuario no es un secretario e intenta firmar en su propia hoja
            if (Auth::user()->secretary != null) {
                if (Auth::user()->secretary->id == $signature_sheet->secretary_id) {
                    // ya estaba bloqueado; ahora además queda anotado
                    Activity::record(
                        ActivityAction::SelfReviewBlocked,
                        $signature_sheet,
                        Auth::user(),
                        details: ['attempted' => ActivityAction::SignatureSigned->value],
                        flags: [ActivityFlag::SelfReview],
                        deadline: false,
                    );

                    Auth::logout();
                    $sign_message = 'Eres el secretario de esta reunión, no es necesario que firmes en tu propia hoja de firmas.';

                    return redirect()->route('sign.finish', [])->with('sign_message', $sign_message);
                }
            }

            // comprobamos que no haya firmado con anterioridad
            if (! Auth::user()->signatureSheets->contains($signature_sheet->id)) {

                // firma
                DB::transaction(function () use ($signature_sheet) {
                    Auth::user()->signatureSheets()->attach($signature_sheet->id);

                    Activity::record(ActivityAction::SignatureSigned, $signature_sheet, Auth::user(), details: ['meeting_request_id' => $signature_sheet->meeting_request_id]);
                });

                Auth::logout();

                $sign_message = 'Tu firma ha sido validada y registrada correctamente.';

                return redirect()->route('sign.finish')->with('sign_message', $sign_message);

            }

            Auth::logout();

            $sign_message = 'Parece ser que ya has firmado en esta reunión. Si consideras que es un error, contacta con el secretario de tu comité.';

            return redirect()->route('sign.finish', [])->with('sign_message', $sign_message);

        }

        RateLimiter::hit($throttle_key);

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
