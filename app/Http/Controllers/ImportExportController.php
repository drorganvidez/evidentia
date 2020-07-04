<?php

namespace App\Http\Controllers;

use App\Role;
use App\User;
use Illuminate\Http\Request;
use App\Imports\UsersImport;
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
            $path = Storage::putFileAs($instance . '/imports/', $file, $file->getClientOriginalName());

            // importamos los usuarios a la base de datos
            Excel::import(new UsersImport, $path);

            // borramos el XLS subido
            Storage::delete($path);

            // seteamos todos los usuarios como ESTUDIANTE por defecto
            $this->set_student_rol();

            return "ha funcionao?";
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

}
