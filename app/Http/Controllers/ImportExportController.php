<?php

namespace App\Http\Controllers;

use App\Exports\EvidencesExport;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use App\Imports\UsersImport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Exception;

class ImportExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:LECTURE');
    }

    public function import()
    {
        $instance = \Instantiation::instance();
        $route = route('lecture.import.save',$instance);

        return view('importexport.import',
            ['instance' => $instance, 'route' => $route]);
    }

    public function import_save(Request $request)
    {
        $instance = \Instantiation::instance();
        $path = null;

        try {
            // almacenamos en disco el archivo XLS de importación
            $file = $request->file('xls');
            if($file != null) {
                $path = Storage::putFileAs($instance . '/imports/', $file, $file->getClientOriginalName());

                // importamos los usuarios a la base de datos
                Excel::import(new UsersImport, $path);

                // borramos el XLS subido
                Storage::delete($path);

                // seteamos todos los usuarios como ESTUDIANTE por defecto
                $this->set_student_rol();

                return redirect()->route('lecture.user.list', $instance)->with('success', 'Alumnos importados con éxito');
            }else{
                return redirect()->route('lecture.import',$instance)->with('error', 'No se seleccionó ningún archivo');
            }
        }catch (\Exception $e){
            // borramos cualquier archivo subido
            Storage::delete($path);
            return redirect()->route('lecture.import',$instance)->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }

    }

    public function set_student_rol()
    {
        $users = User::all();
        foreach ($users as $user){

            // si un usuario no tiene roles es que se acaba de importar
            if($user->roles->isEmpty()){
                $student_role = Role::where('rol','STUDENT')->get();
                $user->roles()->attach($student_role);
            }
        }
    }

    public function export()
    {
        $instance = \Instantiation::instance();
        $route = route('lecture.export.save',$instance);

        return view('importexport.export',
            ['instance' => $instance, 'route' => $route]);
    }

    public function export_save(Request $request)
    {
        $instance = \Instantiation::instance();
        $evidences_select = $request->input('evidences');
        $meetings_select = $request->input('meetings');
        $events_select = $request->input('events');
        $bonus = $request->input('bonus');

        try{
            // limpiar búfer de salida
            ob_end_clean();
            return Excel::download(new EvidencesExport($evidences_select,$meetings_select,$events_select,$bonus), 'evidencias' . Carbon::now() . '.xlsx');
        }catch(\Exception $e){
            return redirect()->route('lecture.export',$instance)->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }

    }

}
