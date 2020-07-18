<?php

namespace App\Http\Controllers;

use App\Comittee;
use App\Coordinator;
use App\Evidence;
use App\Meeting;
use App\Role;
use App\Secretary;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:LECTURE|PRESIDENT');
    }

    public function user_list()
    {
        $instance = \Instantiation::instance();
        $users = User::all();

        return view('manage.user_list',
            ['instance' => $instance, 'users' => $users]);
    }

    public function evidence_list()
    {
        $instance = \Instantiation::instance();
        $evidences = Evidence::evidences_not_draft();

        return view('manage.evidence_list',
            ['instance' => $instance, 'evidences' => $evidences]);
    }

    public function meeting_list()
    {
        $instance = \Instantiation::instance();
        $meetings = Meeting::all();

        return view('manage.meeting_list',
            ['instance' => $instance, 'meetings' => $meetings]);
    }

    public function user_management($instance,$id)
    {
        $instance = \Instantiation::instance();
        $user = User::find($id);

        $route = null;
        if(Auth::user()->hasRole('PRESIDENT')){
            $route = route('president.user.management.save', $instance);
        }else{
            $route = route('lecture.user.management.save', $instance);
        }

        $roles = Role::all();
        $comittees = Comittee::all();

        return view('manage.user_management',
            ['instance' => $instance, 'route' => $route, 'user' => $user, 'roles' => $roles, 'comittees' => $comittees]);
    }

    public function user_management_save(Request $request)
    {

        $instance = \Instantiation::instance();
        $user = User::find($request->input('_id'));

        // guardar bloqueo
        if($request->input('block') == 'on'){
            $user->block = true;
        }else{
            $user->block = false;
        }
        $user->save();

        // aqui se cambian los roles
        $roles_id = $request->input('roles',[]);
        $comittee_id = $request->input('comittee');
        if(Comittee::find($comittee_id) == null){
            return redirect()->route('lecture.user.management',['instance' => $instance, 'id' => $user->id])->with('error','Comité no válido.');
        }

        // elimino toda asociación previa
        DB::table('coordinators')->where(['user_id' => $user->id])->delete();
        DB::table('secretaries')->where(['user_id' => $user->id])->delete();

        // elimino roles anteriores
        $user->roles()->detach();

        // asigno los nuevos roles
        foreach($roles_id as $rol_id)
        {
            $rol = Role::find($rol_id);
            $user->roles()->attach($rol);
            $user->save();
        }

        // ¿tiene rol de coordinador?
        if($user->hasRole('COORDINATOR')){
            DB::table('coordinators')->insert(['comittee_id' => $comittee_id, 'user_id' => $user->id]);
        }

        // ¿tiene rol de secretario?
        if($user->hasRole('SECRETARY')){
            DB::table('secretaries')->insert(['comittee_id' => $comittee_id, 'user_id' => $user->id]);
        }

        $user->save();

        // el profesor tiene potestad para cambiar datos sensibles de un alumno
        if(Auth::user()->hasRole('LECTURE')) {

            $request->validate([
                'name' => 'required|max:255',
                'surname' => 'required|max:255'
            ]);

            // si se cambia el uvus, comprueba que el nuevo sea único
            if ($request->input('username') != $user->username) {
                $request->validate([
                    'username' => 'required|max:255|unique:users',
                ]);
            }

            // si se cambia el dni, comprueba que el nuevo sea único
            if ($request->input('dni') != $user->dni) {
                $request->validate([
                    'dni' => 'required|max:255|unique:users',
                ]);
            }

            // si se cambia el email, comprueba que el nuevo sea único
            if ($request->input('email') != $user->email) {
                $request->validate([
                    'email' => 'required|max:255|unique:users',
                ]);
            }

            $user->username = $request->input('username');
            $user->dni = $request->input('dni');
            $user->name = $request->input('name');
            $user->surname = $request->input('surname');
            $user->email = $request->input('email');

            // si se cambia la contraseña
            if ($request->input('password') != "") {
                $request->validate([
                    'password' => 'required|confirmed|min:6'
                ]);
                $user->password = Hash::make($request->input('password'));
            }

            $user->save();

        }

        return redirect()->route('lecture.user.management',['instance' => $instance, 'id' => $user->id])->with('success','Usuario actualizado con éxito');
    }
}
