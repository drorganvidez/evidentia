<?php

namespace App\Http\Controllers;

use App\Mail\PasswordReset;
use App\Models\Token;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function reset()
    {

        return view('auth.passwords.reset',
            []);

    }

    public function reset_p(Request $request)
    {

        $request->validate([
            'email' => 'required'
        ]);

        $user = User::where('email',$request->email)->first();

        if($user != null){

            // medida de seguridad: si se genera un nuevo token, todos los anteriores se invalidan
            $tokens = Token::where("user_id",$user->id)->get();
            foreach ($tokens as $t){
                $t->used = true;
                $t->save();
            }

            $token_str = Str::random(255);

            $token = Token::create([
                "token" => $token_str,
                "used" => 0,
                "valid_until_timestamp" => \Carbon\Carbon::now()->addHours(24),
                "user_id" => $user->id
            ]);

            $token->save();

            try{
                Mail::to($user)->send(new PasswordReset($token,$user));
            }catch(\Exception $e){

            }
        }

        return redirect()->route('login')->with('light', 'Si el email se encuentra en nuestros registros, recibirás un correo con instrucciones para restablecer tu contraseña.');
    }

    public function update($token)
    {

        $token_entity = Token::where("token", $token)->first();

        if($token_entity != null){

            if($token_entity->is_valid()){
                return view('auth.passwords.update',
                    ['token' => $token]);
            }

            return redirect()->route('login')->with('error', 'El token no es válido o ha caducado.');

        }

        return redirect()->route('login');

    }

    public function update_p(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:6'
        ]);

        $token_entity = Token::where("token", $request->route('token'))->first();

        if($token_entity != null){

            // cambio de contraseña
            $user = User::find($token_entity->user_id);
            $user->password = Hash::make($request->input('password'));
            $user->save();

            // el token deja de ser válido
            $token_entity->used = true;
            $token_entity->save();

            return redirect()->route('login')->with('success', 'Contraseña cambiada con éxito. Ahora puedes iniciar sesión.');
        }else{
            return redirect()->route('login')->with('error', 'El token no es válido o ha caducado.');
        }


    }
}
