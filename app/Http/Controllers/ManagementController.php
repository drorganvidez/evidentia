<?php

namespace App\Http\Controllers;

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
use Illuminate\Validation\Rule;

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

        // los nombres resultantes no pueden repetirse, igual que al crear un comité
        $names = Committee::all()->map(fn ($committee) => mb_strtolower(trim($request->input('name_'.$committee->id) ?: $committee->name)));
        if ($names->duplicates()->isNotEmpty()) {
            return redirect()->route($returned_route)->with('error', 'Dos comités no pueden llamarse igual.');
        }

        foreach (Committee::all() as $committee) {

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
        $this->abort_unless_can_manage($user);

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
        $user = User::findOrFail($request->input('user_id'));
        $this->abort_unless_can_manage($user);

        // el presidente no puede dar el rol de profesor
        $roles_id = Role::whereIn('id', $request->input('roles', []))
            ->when(! Auth::user()->hasRole('LECTURE'), fn ($query) => $query->where('rol', '!=', 'LECTURE'))
            ->pluck('id');
        $needs_committee = Role::whereIn('id', $roles_id)->whereIn('rol', ['COORDINATOR', 'SECRETARY'])->exists();

        // validamos todo antes de modificar nada
        $request->validate([
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'username' => ['required', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|confirmed|min:6',
            'committee' => $needs_committee ? 'required|exists:committees,id' : 'nullable',
        ], [
            'committee.required' => 'Comité no válido.',
            'committee.exists' => 'Comité no válido.',
        ]);

        $committee_id = $request->input('committee');

        // guardar bloqueo
        $user->block = $request->input('pass') == 'on' ? false : true;

        // asignar nuevos roles
        $user->roles()->sync($roles_id);
        $user->load('roles');

        // asignar coordinador o secretario, conservando su fila (actas, convocatorias, listas...)
        foreach (['coordinators' => 'COORDINATOR', 'secretaries' => 'SECRETARY'] as $table => $role) {
            if ($user->hasRole($role)) {
                DB::table($table)->updateOrInsert(['user_id' => $user->id], ['committee_id' => $committee_id]);
            } else {
                DB::table($table)->where('user_id', $user->id)->delete();
            }
        }

        $user->username = $request->input('username');
        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->email = $request->input('email');
        $user->clean_name = \StringUtilites::clean(trim($request->input('name')));
        $user->clean_surname = \StringUtilites::clean(trim($request->input('surname')));

        // cambio de contraseña (si se indica)
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        // redirección según el rol del que ejecuta la acción
        if (Auth::user()->hasRole('PRESIDENT')) {
            return redirect()->route('president.user.list', ['id' => $user->id])
                ->with('success', 'Usuario actualizado con éxito');
        }

        return redirect()->route('lecture.user.list', ['id' => $user->id])
            ->with('success', 'Usuario actualizado con éxito');
    }

    // el presidente no puede editar los usuarios de tipo profesor
    private function abort_unless_can_manage(User $user): void
    {
        if (! Auth::user()->hasRole('LECTURE') && $user->hasRole('LECTURE')) {
            abort(404);
        }
    }

    public function user_management_new(Request $request)
    {

        $request->validate([
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
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

        $list = Auth::user()->hasRole('PRESIDENT') ? 'president.user.list' : 'lecture.user.list';

        return redirect()->route($list)->with('success', 'Usuario creado con éxito');

    }

    public function user_management_delete_all(Request $request)
    {
        $users = $this->user_service->all_except_logged();

        foreach ($users as $user) {
            $this->user_service->delete($user->id);
        }

        return redirect()->route('lecture.user.list', [])->with('success', 'Usuarios borrados con éxito');
    }
}
