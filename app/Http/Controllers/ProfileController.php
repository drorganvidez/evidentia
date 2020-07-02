<?php

namespace App\Http\Controllers;

use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function view()
    {
        $instance = \Instantiation::instance();

        $route_upload_info = route('profile.upload.info',$instance);
        $route_upload_biography = route('profile.upload.biography',$instance);
        $route_upload_pass = route('profile.upload.pass',$instance);

        return view('profile.view',
            ['instance' => $instance, 'route_upload_info' => $route_upload_info,
                'route_upload_biography' => $route_upload_biography,
                'route_upload_pass' => $route_upload_pass]);
    }

    public function upload_info(Request $request)
    {
        $instance = \Instantiation::instance();

        $user = Auth::user();

        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'email' => 'required|max:255|unique:users'
        ]);

        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->email = $request->input('email');

        $user->save();

        return redirect()->route('profile.view',$instance)->with('success', 'Datos personales editados con éxito.');

    }

    public function upload_biography(Request $request)
    {
        $instance = \Instantiation::instance();

        $user = Auth::user();

        $validatedData = $request->validate([
            'biography' => ['required',new MinCharacters(10),new MaxCharacters(20000)]
        ]);

        $user->biography = $request->input('biography');

        $user->save();

        return redirect()->route('profile.view',$instance)->with('success', 'Biografía editada con éxito.');

    }

    public function upload_pass(Request $request)
    {

        $instance = \Instantiation::instance();

        $user = Auth::user();

        $validatedData = $request->validate([
            'password' => 'required|confirmed|min:6'
        ]);

        $user->password = Hash::make($request->input('password'));

        $user->save();

        return redirect()->route('profile.view',$instance)->with('success', 'Contraseña cambiada con éxito.');

    }
}
