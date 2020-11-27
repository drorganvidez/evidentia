<?php

namespace App\Http\Controllers;

use App\Avatar;
use App\Evidence;
use App\File;
use App\Proof;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

        try {

            if($request->has('avatar')) {

                // almacenamos en disco el avatar
                $file = $request->file('avatar');
                $path = Storage::putFileAs($instance . '/avatares/' . $user->username, $file, $this->file_name_parser($file));

                // almacenamos en la BBDD la información del archivo
                $file_entity = File::create([
                    'name' => $this->file_name_parser($file),
                    'type' => strtolower($file->getClientOriginalExtension()),
                    'route' => $path,
                    'size' => $file->getSize(),
                ]);

                // cómputo del sello
                $file_entity = \Stamp::compute_file($file_entity);
                $file_entity->save();

                // borramos el avatar antiguo (si lo tuviera)
                if ($user->avatar != null){
                    Storage::delete($user->avatar->file->route);
                    $user->avatar->file->delete();
                    $user->avatar->delete();
                }

                // almacenamos en la BBDD el avatar
                $avatar = Avatar::create([
                    'user_id' => $user->id,
                    'file_id' => $file_entity->id
                ]);

                $avatar->save();

            }

        } catch (\Exception $e) {
            return $e;
        }


        return redirect()->route('profile.view',$instance)->with('success', 'Datos personales editados con éxito.');

    }

    private function file_name_parser($file){
        $name = $file->getClientOriginalName();
        $file_from_ddbb = File::where("name",$name)->first();

        // si ya hay un avatar con ese nombre, ponerle otro nombre al nuevo
        if($file_from_ddbb != null){
            $name .= " (copy)." . strtolower($file->getClientOriginalExtension());
        }

        return $name;
    }

    public function upload_biography(Request $request)
    {
        $instance = \Instantiation::instance();

        $user = Auth::user();

        $request->validate([
            'biography' => ['required',new MinCharacters(10),new MaxCharacters(20000)],
            'participation' => 'required|numeric|min:1|max:3',
        ]);

        $user->biography = $request->input('biography');
        $user->participation = $request->input('participation');

        $user->save();

        return redirect()->route('profile.view',$instance)->with('success', 'Biografía editada con éxito.');

    }

    public function upload_pass(Request $request)
    {

        $instance = \Instantiation::instance();

        $user = Auth::user();

        $request->validate([
            'password' => 'required|confirmed|min:6'
        ]);

        $user->password = Hash::make($request->input('password'));

        $user->save();

        return redirect()->route('profile.view',$instance)->with('success', 'Contraseña cambiada con éxito.');

    }

    public function profiles_view($instance,$id)
    {
        $instance = \Instantiation::instance();
        $user = User::find($id);

        return view('profile.generalview',
            ['instance' => $instance, 'user' => $user]);
    }

    public function evidences_view($instance,$id_user, $id_evidence)
    {
        $instance = \Instantiation::instance();
        $user = User::find($id_user);
        $evidence = Evidence::find($id_evidence);

        if($evidence == null){
            return redirect()->route('home',['instance' => \Instantiation::instance()]);
        }else if($evidence->status == "DRAFT"){
            return redirect()->route('home',['instance' => \Instantiation::instance()]);
        }

        return view('profile.profile_evidence_view',
            ['instance' => $instance, 'user' => $user, 'evidence' => $evidence]);
    }
}
