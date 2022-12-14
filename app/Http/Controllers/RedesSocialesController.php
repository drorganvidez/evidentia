<?php

namespace App\Http\Controllers;

use App\Models\RedSocial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RedesSocialesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {

        return view('redesSociales.createandedit', ['route' => route('admin.redesSociales.new')]);
    }

    public function new(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|unique:redsocial',
            'password' => 'required',
        ]);

        $redSocial = new RedSocial();

        $redSocial->name = $request->name;
        $redSocial->password = $request->password;

        // DB::connection()->getPdo()->exec("CREATE DATABASE IF NOT EXISTS `{$redSocial->database}`");
        // DB::connection()->getPdo()->exec("ALTER SCHEMA `{$redSocial->database}`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");

        // \Instantiation::set($redSocial);

        // try {
        //     DB::connection()->getPdo();
        //     \Instantiation::migrate();
        //     Artisan::call('db:seed',
        //         [
        //             '--class' => 'SampleSeeder',
        //             '--database' => 'redsocial'
        //         ]);
        // } catch (\Exception $e) {

        //     \Instantiation::set_default_connection();
        //     DB::statement("DROP DATABASE `{$redSocial->database}`");
        //     return back()->withInput()->with('error', 'Conexión fallida, revise los parámetros de configuración de la base de datos.')->setStatusCode(422);

        // }

        // \Instantiation::set_default_connection();

        $redSocial->save();

        $message = 'Contraseña de red social guarda con éxito';
        return redirect()->route('admin.redesSociales.manage')->with('success', $message)->setStatusCode(200);
    }

    public function edit($id)
    {
        $redSocial = RedSocial::find($id);
        return view('redesSociales.createandedit', ['redsocial' => $redSocial, 'edit' => true, 'route' => route('admin.redesSociales.manage.save')]);
    }

    public function save(Request $request)
    {

        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('redsocial')->ignore($request->_id)],
            'password' => 'required',
        ]);

        $redSocial = RedSocial::where('id', $request->_id)->first();
        $redSocial->name = $request->name;
        $redSocial->password = $request->password;

        // \Instantiation::set($redSocial);

        // try {
        //     DB::connection()->getPdo();
            
        // } catch (\Exception $e) {

        //     \Instantiation::set_default_connection();
        //     return back()->withInput()->with('error', 'Conexión fallida, revise los parámetros de configuración de la base de datos.');

        // }

        // \Instantiation::set_default_connection();
        
        $redSocial->save();

        return redirect()->route('admin.redesSociales.manage')->with('success', 'Red social actualizada con éxito.');
    }

    public function manage()
    {

        $redSocial = RedSocial::all();

        return view('redesSociales.manage', ['redsocial' => $redSocial]);
    }

    public function delete($id)
    {
        $redSocial = RedSocial::where('id', $id)->first();
        return view('redesSociales.delete', ['redsocial' => $redSocial]);
    }

    public function remove(Request $request)
    {
        $redSocial = RedSocial::where('id', $request->id)->first();

        $request->validate([
            'name' => 'in:' . $redSocial->name
        ]);

        // DB::statement("DROP DATABASE `{$redSocial->database}`");

        $redSocial->delete();

        return redirect()->route('admin.redesSociales.manage')->with('success', 'Red social eliminada con éxito.');
    }

}
