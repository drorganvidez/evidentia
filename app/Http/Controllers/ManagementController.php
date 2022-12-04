<?php

namespace App\Http\Controllers;

use App\Exports\ManagementEvidencesExport;
use App\Exports\ManagementStudentExport;
use App\Http\Services\UserService;
use App\Models\Comittee;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\Transaction;
use App\Models\Role;
use App\Models\Secretary;
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
        $this->middleware('checkroles:LECTURE|PRESIDENT');
        $this->user_service = new UserService();
    }

    public function user_list()
    {
        $instance = \Instantiation::instance();
        $users = User::all();

        // el presidente no puede editar los usuarios de tipo profesor
        $filtered_users = collect();
        if (Auth::user()->hasRole('PRESIDENT')) {
            $users->each(function ($item, $key) use ($filtered_users) {
                if (!$item->hasRole('LECTURE')) {
                    $filtered_users->push($item);
                }
            });
        } else {
            $filtered_users = $users;
        }

        return view('manage.user_list',
            ['instance' => $instance, 'users' => $filtered_users]);
    }



    public function evidence_list()
    {
        $instance = \Instantiation::instance();
        $evidences = Evidence::evidences_not_draft();

        return view('manage.evidence_list',
            ['instance' => $instance, 'evidences' => $evidences]);
    }

    public function accept($instance, $id)
    {
        $instance = \Instantiation::instance();

        $transaction = Transaction::find($id);
        $transaction->status = 'ACCEPTED';
        $transaction->save();


        return redirect()->route('president.transaction.list', $instance)->with('success', "'Transacción aceptada con éxito.");
    }
    public function reject($instance, $id)
    {
        $instance = \Instantiation::instance();

        $transaction = Transaction::find($id);
        $transaction->status = 'REJECTED';
        $transaction->save();

        return redirect()->route('president.transaction.list', $instance)->with('success', 'Transacción rechazada con éxito .');
    }
    public function transaction_list()
    {
        $instance = \Instantiation::instance();
        $transactions = Transaction::all();
        $total = 0;
        $noAceptado = 0;
        $aceptado = 0;
        $pendiente = 0;

        for ($i = 0; $i < count($transactions); $i++) {
            $total = $total + $transactions[$i]->amount;
            if ($transactions[$i]->status == 'ACCEPTED') {
                $aceptado = $aceptado + $transactions[$i]->amount;
            } elseif ($transactions[$i]->status == 'REJECTED') {
                $noAceptado = $noAceptado + $transactions[$i]->amount;
            } else {
                $pendiente = $pendiente + $transactions[$i]->amount;
            }
        }

        return view('manage.transaction_list',
            ['instance' => $instance, 'transactions' => $transactions, 'total' => $total, 'noAceptado' => $noAceptado, 'aceptado' => $aceptado, 'pendiente' => $pendiente]);
    }

    public function meeting_list()
    {
        $instance = \Instantiation::instance();
        $meetings = Meeting::all();

        return view('manage.meeting_list',
            ['instance' => $instance, 'meetings' => $meetings]);
    }

    public function comittee_list()
    {
        $instance = \Instantiation::instance();
        $comittees = Comittee::all();

        $route = null;
        $route_new = null;
        $route_remove = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $route = route('president.comittee.management.save', $instance);
            $route_new = route('president.comittee.management.new', $instance);
            $route_remove = route('president.comittee.management.remove', $instance);
        } else {
            $route = route('lecture.comittee.management.save', $instance);
            $route_new = route('lecture.comittee.management.new', $instance);
            $route_remove = route('lecture.comittee.management.remove', $instance);
        }

        return view('manage.comittee_list',
            ['instance' => $instance, 'comittees' => $comittees, 'route' => $route, 'route_new' => $route_new, 'route_remove' => $route_remove]);
    }

    public function comittee_new(Request $request)
    {
        $instance = \Instantiation::instance();
        $request->validate([
            'icon' => 'max:255',
            'name' => 'required|max:255|unique:comittees'
        ]);

        Comittee::create([
            'icon' => $request->input('icon'),
            'name' => $request->input('name')
        ]);

        if (Auth::user()->hasRole('PRESIDENT')) {
            return redirect()->route('president.comittee.list', $instance)->with('success', 'Comité creado con éxito.');
        } else {
            return redirect()->route('lecture.comittee.list', $instance)->with('success', 'Comité creado con éxito.');
        }
    }

    public function comittee_save(Request $request)
    {
        $instance = \Instantiation::instance();
        $returned_route = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $returned_route = "president.comittee.list";
        } else {
            $returned_route = "lecture.comittee.list";
        }

        // por sl algún usuario avispao modifica los ID en el HTML
        try {
            foreach (Comittee::all() as $comittee) {


            }
        } catch (\Exception $e) {
            return redirect()->route($returned_route, $instance)->with('error', 'Error en la integridad de los comités.' . $e->getMessage());
        }

        foreach (Comittee::all() as $comittee) {

            /*
            $request->validate([
                'icon_' . $comittee->id => 'max:255',
                'name_' . $comittee->id => 'required|max:255|unique:comittees'
            ]);
            */

            $new_icon = $request->input('icon_' . $comittee->id);
            $new_name = $request->input('name_' . $comittee->id);

            if ($new_name != "") {

                $saved_comittee = Comittee::find($comittee->id);
                $saved_comittee->icon = $new_icon;
                $saved_comittee->name = $new_name;

                $saved_comittee->save();
            }
        }

        return redirect()->route($returned_route, $instance)->with('success', 'Comités guardados con éxito.');
    }

    public function comittee_remove(Request $request)
    {
        $instance = \Instantiation::instance();
        $returned_route = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $returned_route = "president.comittee.list";
        } else {
            $returned_route = "lecture.comittee.list";
        }

        $comittee = Comittee::find($request->input('_id'));
        if ($comittee->can_be_removed()) {
            $comittee->delete();
            return redirect()->route($returned_route, $instance)->with('success', 'Comité eliminado con éxito.');
        } else {
            return redirect()->route($returned_route, $instance)->with('error', 'El comité no ha podido ser eliminado.');
        }

    }

    public function user_management($instance, $id)
    {
        $instance = \Instantiation::instance();
        $user = User::findOrFail($id);

        $route = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $route = route('president.user.management.save', $instance);
        } else {
            $route = route('lecture.user.management.save', $instance);
        }


        // el presidente no puede crear usuarios de tipo profesor
        $roles = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $roles = Role::where('rol', '!=', 'LECTURE')->get();
        } else {
            $roles = Role::all();
        }

        $comittees = Comittee::all();

        return view('manage.user_management',
            ['instance' => $instance, 'route' => $route, 'user' => $user, 'roles' => $roles, 'comittees' => $comittees]);
    }

    public function user_management_save(Request $request)
    {

        $instance = \Instantiation::instance();
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
        $comittee_id = $request->input('comittee');
        if (Comittee::find($comittee_id) == null) {
            return redirect()->route('lecture.user.management', ['instance' => $instance, 'id' => $user->id])->with('error', 'Comité no válido.');
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
            DB::table('coordinators')->insert(['comittee_id' => $comittee_id, 'user_id' => $user->id]);
        }

        // ¿tiene rol de secretario?
        if ($user->hasRole('SECRETARY')) {
            DB::table('secretaries')->insert(['comittee_id' => $comittee_id, 'user_id' => $user->id]);
        }

        $user->save();

        // el profesor tiene potestad para cambiar datos sensibles de un alumno
        if (Auth::user()->hasRole('LECTURE')) {

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
            if ($request->input('password') != "") {
                $request->validate([
                    'password' => 'required|confirmed|min:6'
                ]);
                $user->password = Hash::make($request->input('password'));
            }

            $user->save();

        }

        if ($user->hasRole('PRESIDENT')) {
            return redirect()->route('president.user.list', ['instance' => $instance, 'id' => $user->id])->with('success', 'Usuario actualizado con éxito');
        }

        return redirect()->route('lecture.user.list', ['instance' => $instance, 'id' => $user->id])->with('success', 'Usuario actualizado con éxito');
    }

    public function user_management_new(Request $request)
    {

        $instance = \Instantiation::instance();

        $request->validate([
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'email' => 'required|max:255|unique:users',
            'username' => 'required|max:255|unique:users'
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

        return redirect()->route('lecture.user.list', ['instance' => $instance, 'id' => $user->id])->with('success', 'Usuario creado con éxito');

    }

    public function user_management_delete_all(Request $request)
    {
        $users = $this->user_service->all_except_logged();

        foreach ($users as $user) {
            $this->user_service->delete($user->id);
        }

        return redirect()->route('lecture.user.list', ['instance' => \Instantiation::instance()])->with('success', 'Usuarios borrados con éxito');
    }

    public function evidences_export($instance, $ext)
    {
        try {
            ob_end_clean();
            if (!in_array($ext, ['csv', 'pdf', 'xlsx'])) {
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new ManagementEvidencesExport(), 'evidencias-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }


    public function management_student_export($instance, $ext)
    {
        try {
            ob_end_clean();
            if(!in_array($ext, ['csv', 'pdf', 'xlsx'])){
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new ManagementStudentExport(), 'alumnos-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }
}
