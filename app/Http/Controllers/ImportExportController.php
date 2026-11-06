<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Exports\EvidencesExport;
use App\Facades\Activity;
use App\Http\Middleware\CheckRoles;
use App\Imports\UsersImport;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':LECTURE,PRESIDENT');
    }

    public function import()
    {

        $route = route('lecture.import.save');

        return view('importexport.import',
            ['route' => $route]);
    }

    public function import_save(Request $request)
    {
        $user = Auth::user();

        $token = $request->session()->token();
        $tmp = 'tmp/'.$user->username.'/'.$token.'/';

        if (empty(Storage::files($tmp))) {
            return redirect()->route('lecture.import')->with('error', 'Sube primero el archivo con los alumnos.');
        }

        // los importados son los que quedan por encima del último usuario de antes
        $before = User::max('id') ?? 0;

        try {
            foreach (Storage::files($tmp) as $filename) {
                $name = pathinfo($filename, PATHINFO_FILENAME);
                $type = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $path = $tmp.$name.'.'.$type;

                DB::transaction(function () use ($path, $name, $type, $before) {
                    Excel::import(new UsersImport, $path);
                    $this->set_student_rol();

                    $created = User::where('id', '>', $before)->pluck('id')->all();
                    Activity::record(ActivityAction::ImportUsers, details: ['file' => $name.'.'.$type, 'created' => count($created)], targets: $created);
                });
                Storage::delete($path);

                break;
            }

        } catch (\Exception $e) {
            return redirect()->route('lecture.import')->with('error', 'Ocurrió un error: '.$e->getMessage());
        }

        return redirect()->route('lecture.user.list')->with('success', 'Alumnos importados con éxito');

    }

    public function set_student_rol()
    {
        $users = User::all();
        foreach ($users as $user) {

            // si un usuario no tiene roles es que se acaba de importar
            if ($user->roles->isEmpty()) {
                $student_role = Role::where('rol', 'STUDENT')->get();
                $user->roles()->attach($student_role);
            }
        }
    }

    public function export()
    {

        $route = Auth::user()->hasRole('LECTURE') ? route('lecture.export.save') : route('president.export.save');

        return view('importexport.export',
            ['route' => $route]);
    }

    public function export_save(Request $request)
    {

        $evidences_select = $request->input('evidences');
        $meetings_select = $request->input('meetings');
        $events_select = $request->input('events');
        $bonus = $request->input('bonus');

        try {
            // limpiar búfer de salida
            if (ob_get_level()) {
                ob_end_clean();
            }

            $sections = array_filter([
                'Evidencias' => $evidences_select, 'Reuniones' => $meetings_select, 'Eventos' => $events_select, 'Bono de horas' => $bonus,
            ], fn ($value) => filled($value));
            Activity::record(ActivityAction::ExportEvidences, details: ['sections' => array_keys($sections)]);

            return Excel::download(new EvidencesExport($evidences_select, $meetings_select, $events_select, $bonus), 'evidencias'.Carbon::now().'.xlsx');
        } catch (\Exception $e) {
            return redirect()->route('lecture.export')->with('error', 'Ocurrió un error: '.$e->getMessage());
        }

    }
}
