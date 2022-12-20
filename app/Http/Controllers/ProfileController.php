<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function data()
    {
        return view('profile.data');
    }

    public function data_p(Request $request)
    {
        $instance = \Instantiation::instance();

        $user = Auth::user();

        $request->validate([
            'name' => 'required|max:255',
            'surname' => 'required|max:255'
        ]);

        // si el usuario cambia el email, comprueba que el nuevo sea único
        if($request->input('email') != $user->email) {
            $request->validate([
                'email' => 'required|max:255|unique:users',
            ]);
        }

        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->email = $request->input('email');

        $user->clean_name = strtoupper(trim(preg_replace('~[^0-9a-z]+~i', '', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($request->input('name'), ENT_QUOTES, 'UTF-8'))), ' '));
        $user->clean_surname = strtoupper(trim(preg_replace('~[^0-9a-z]+~i', '', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($request->input('surname'), ENT_QUOTES, 'UTF-8'))), ' '));

        $user->save();

        return redirect()->route('profile.data',$instance)->with('success', 'Datos personales editados con éxito.');
    }

    public function avatar()
    {
        return view('profile.avatar');
    }

    public function password()
    {
        return view('profile.password');
    }

    public function password_p(Request $request)
    {
        $instance = \Instantiation::instance();

        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => [
                'required',
                'min:8',
                'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%.@]).*$/',
                'confirmed'
            ]
        ]);

        if (!Hash::check($request->get('current_password'), $user->password))
        {
            return back()->with('error', "La contraseña actual que has introducido no es correcta");
        }

        $user->password = Hash::make($request->input('password'));

        $user->save();

        return redirect()->route('profile.password',$instance)->with('success', 'Contraseña cambiada con éxito.');
    }
}
