<?php

namespace App\Http\Controllers;

use App\Exports\ManagementEvidencesExport;
use App\Exports\ManagementStudentExport;
use App\Http\Middleware\CheckRoles;
use App\Http\Services\UserService;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class ManagementController extends Controller
{
    private $user_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':LECTURE,PRESIDENT');
        $this->user_service = new UserService;
    }

    public function user_list()
    {

        $users = User::all();

        // el presidente no puede editar los usuarios de tipo profesor
        $filtered_users = collect();
        if (Auth::user()->hasRole('PRESIDENT')) {
            $users->each(function ($item, $key) use ($filtered_users) {
                if (! $item->hasRole('LECTURE')) {
                    $filtered_users->push($item);
                }
            });
        } else {
            $filtered_users = $users;
        }

        return view('manage.user_list',
            ['users' => $filtered_users]);
    }

    public function evidence_list()
    {

        $evidences = Evidence::evidencesNotDraft();

        return view('manage.evidence_list',
            ['evidences' => $evidences]);
    }

    public function meeting_list()
    {

        $meetings = Meeting::all();

        return view('manage.meeting_list',
            ['meetings' => $meetings]);
    }

    public function committee_list()
    {

        $committees = Committee::all();

        $route = null;
        $route_new = null;
        $route_remove = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $route = route('president.committee.management.save');
            $route_new = route('president.committee.management.new');
            $route_remove = route('president.committee.management.remove');
        } else {
            $route = route('lecture.committee.management.save');
            $route_new = route('lecture.committee.management.new');
            $route_remove = route('lecture.committee.management.remove');
        }

        return view('manage.committee_list',
            ['committees' => $committees, 'route' => $route, 'route_new' => $route_new, 'route_remove' => $route_remove]);
    }

    public function committee_new(Request $request)
    {

        $request->validate([
            'icon' => 'max:255',
            'name' => 'required|max:255|unique:committees',
        ]);

        Committee::create([
            'icon' => $request->input('icon'),
            'name' => $request->input('name'),
        ]);

        if (Auth::user()->hasRole('PRESIDENT')) {
            return redirect()->route('president.committee.list')->with('success', 'Comité creado con éxito.');
        } else {
            return redirect()->route('lecture.committee.list')->with('success', 'Comité creado con éxito.');
        }
    }

    public function committee_save(Request $request)
    {

        $returned_route = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $returned_route = 'president.committee.list';
        } else {
            $returned_route = 'lecture.committee.list';
        }

        // por sl algún usuario avispao modifica los ID en el HTML
        try {
            foreach (Committee::all() as $committee) {

            }
        } catch (\Exception $e) {
            return redirect()->route($returned_route)->with('error', 'Error en la integridad de los comités.'.$e->getMessage());
        }

        foreach (Committee::all() as $committee) {

            /*
            $request->validate([
                'icon_' . $committee->id => 'max:255',
                'name_' . $committee->id => 'required|max:255|unique:committees'
            ]);
            */

            $new_icon = $request->input('icon_'.$committee->id);
            $new_name = $request->input('name_'.$committee->id);

            if ($new_name != '') {

                $saved_committee = Committee::find($committee->id);
                $saved_committee->icon = $new_icon;
                $saved_committee->name = $new_name;

                $saved_committee->save();
            }
        }

        return redirect()->route($returned_route)->with('success', 'Comités guardados con éxito.');
    }

    public function committee_remove(Request $request)
    {

        $returned_route = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $returned_route = 'president.committee.list';
        } else {
            $returned_route = 'lecture.committee.list';
        }

        $committee = Committee::find($request->input('_id'));
        if ($committee->can_be_removed()) {
            $committee->delete();

            return redirect()->route($returned_route)->with('success', 'Comité eliminado con éxito.');
        } else {
            return redirect()->route($returned_route)->with('error', 'El comité no ha podido ser eliminado.');
        }

    }

    public function user_management($id)
    {

        $user = User::findOrFail($id);

        $route = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $route = route('president.user.management.save');
        } else {
            $route = route('lecture.user.management.save');
        }

        // el presidente no puede crear usuarios de tipo profesor
        $roles = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $roles = Role::where('rol', '!=', 'LECTURE')->get();
        } else {
            $roles = Role::all();
        }

        $committees = Committee::all();

        return view('manage.user_management',
            ['route' => $route, 'user' => $user, 'roles' => $roles, 'committees' => $committees]);
    }

    public function user_management_save(Request $request)
    {

        $user = User::find($request->input('user_id'));

        // guardar bloqueo
        if ($request->input('pass') == 'on') {
            $user->block = false;
        } else {
            $user->block = true;
        }
        $user->save();

        // aqui se cambian los roles
        $roles_id = $request->input('roles', []);
        $committee_id = $request->input('committee');
        if (Committee::find($committee_id) == null) {
            return redirect()->route('lecture.user.management', ['id' => $user->id])->with('error', 'Comité no válido.');
        }

        // elimino toda asociación previa
        DB::table('coordinators')->where(['user_id' => $user->id])->delete();
        DB::table('secretaries')->where(['user_id' => $user->id])->delete();

        // elimino roles anteriores
        $user->roles()->detach();

        // asigno los nuevos roles
        foreach ($roles_id as $rol_id) {
            $rol = Role::find($rol_id);
            $user->roles()->attach($rol);
            $user->save();
        }

        // ¿tiene rol de coordinador?
        if ($user->hasRole('COORDINATOR')) {
            DB::table('coordinators')->insert(['committee_id' => $committee_id, 'user_id' => $user->id]);
        }

        // ¿tiene rol de secretario?
        if ($user->hasRole('SECRETARY')) {
            DB::table('secretaries')->insert(['committee_id' => $committee_id, 'user_id' => $user->id]);
        }

        $user->save();

        // el profesor tiene potestad para cambiar datos sensibles de un alumno
        if (Auth::user()->hasRole('LECTURE')) {

            $request->validate([
                'name' => 'required|max:255',
                'surname' => 'required|max:255',
            ]);

            // si se cambia el uvus, comprueba que el nuevo sea único
            if ($request->input('username') != $user->username) {
                $request->validate([
                    'username' => 'required|max:255|unique:users',
                ]);
            }

            // si se cambia el email, comprueba que el nuevo sea único
            if ($request->input('email') != $user->email) {
                $request->validate([
                    'email' => 'required|max:255|unique:users',
                ]);
            }

            $user->username = $request->input('username');
            $user->name = $request->input('name');
            $user->surname = $request->input('surname');
            $user->email = $request->input('email');
            $user->clean_name = strtoupper(trim(preg_replace('~[^0-9a-z]+~i', '', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($request->input('name'), ENT_QUOTES, 'UTF-8'))), ' '));
            $user->clean_surname = strtoupper(trim(preg_replace('~[^0-9a-z]+~i', '', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($request->input('surname'), ENT_QUOTES, 'UTF-8'))), ' '));

            // si se cambia la contraseña
            if ($request->input('password') != '') {
                $request->validate([
                    'password' => 'required|confirmed|min:6',
                ]);
                $user->password = Hash::make($request->input('password'));
            }

            $user->save();

        }

        if ($user->hasRole('PRESIDENT')) {
            return redirect()->route('president.user.list', ['id' => $user->id])->with('success', 'Usuario actualizado con éxito');
        }

        return redirect()->route('lecture.user.list', ['id' => $user->id])->with('success', 'Usuario actualizado con éxito');
    }

    public function user_management_new(Request $request)
    {

        $request->validate([
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'email' => 'required|max:255|unique:users',
            'username' => 'required|max:255|unique:users',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'surname' => $request->input('surname'),
            'email' => $request->input('email'),
            'username' => $request->input('username'),
            'password' => Hash::make(\Random::getRandomIdentifier(16)),
            'clean_name' => \StringUtilites::clean(trim($request->input('name'))),
            'clean_surname' => \StringUtilites::clean(trim($request->input('surname'))),
        ]);

        $student_role = Role::where('rol', 'STUDENT')->get();
        $user->roles()->attach($student_role);

        return redirect()->route('lecture.user.list', ['id' => $user->id])->with('success', 'Usuario creado con éxito');

    }

    public function user_management_delete_all(Request $request)
    {
        $users = $this->user_service->all_except_logged();

        foreach ($users as $user) {
            $this->user_service->delete($user->id);
        }

        return redirect()->route('lecture.user.list', [])->with('success', 'Usuarios borrados con éxito');
    }

    public function evidences_export($ext)
    {
        try {
            if (ob_get_level()) {
                ob_end_clean();
            }
            if (! in_array($ext, ['csv', 'pdf', 'xlsx'])) {
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }

            return Excel::download(new ManagementEvidencesExport, 'evidencias-'.\Illuminate\Support\Carbon::now().'.'.$ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: '.$e->getMessage());
        }
    }

    public function management_student_export($ext)
    {
        try {
            if (ob_get_level()) {
                ob_end_clean();
            }
            if (! in_array($ext, ['csv', 'pdf', 'xlsx'])) {
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }

            return Excel::download(new ManagementStudentExport, 'alumnos-'.\Illuminate\Support\Carbon::now().'.'.$ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: '.$e->getMessage());
        }
    }
}
