<?php

namespace App\Http\Controllers;

use App\Models\EmpresaColaborativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmpresasColaborativasController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {

        return view('empresasColaborativas.createandedit', ['route' => route('admin.empresasColaborativas.new')]);
    }

    public function new(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|unique:empresa',
            'telephone' => 'required',
            'email' => 'required',
        ]);

        $empresasColaborativa = new EmpresaColaborativa();

        $empresasColaborativa->name = $request->name;
        $empresasColaborativa->telephone = $request->telephone;
        $empresasColaborativa->email = $request->email;

        // DB::connection()->getPdo()->exec("CREATE DATABASE IF NOT EXISTS `{$empresa->database}`");
        // DB::connection()->getPdo()->exec("ALTER SCHEMA `{$empresa->database}`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");

        // \Instantiation::set($empresa);

        // try {
        //     DB::connection()->getPdo();
        //     \Instantiation::migrate();
        //     Artisan::call('db:seed',
        //         [
        //             '--class' => 'SampleSeeder',
        //             '--database' => 'empresa'
        //         ]);
        // } catch (\Exception $e) {

        //     \Instantiation::set_default_connection();
        //     DB::statement("DROP DATABASE `{$empresa->database}`");
        //     return back()->withInput()->with('error', 'Conexión fallida, revise los parámetros de configuración de la base de datos.')->setStatusCode(422);

        // }

        // \Instantiation::set_default_connection();

        $empresasColaborativa->save();

        $message = 'Empresa guarda con éxito';
        return redirect()->route('admin.empresasColaborativas.manage')->with('success', $message)->setStatusCode(200);
    }

    public function edit($id)
    {
        $empresaColaborativa = EmpresaColaborativa::find($id);
        return view('empresasColaborativas.createandedit', ['empresasColaborativa' => $empresaColaborativa, 'edit' => true, 'route' => route('admin.empresasColaborativas.manage.save')]);
    }

    public function save(Request $request)
    {

        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('empresa')->ignore($request->_id)],
            'telephone' => 'required',
            'email' => 'required',
        ]);

        $empresaColaborativa = EmpresaColaborativa::where('id', $request->_id)->first();
        $empresaColaborativa->name = $request->name;
        $empresaColaborativa->telephone = $request->telephone;
        $empresaColaborativa->email = $request->email;

        // \Instantiation::set($empresa);

        // try {
        //     DB::connection()->getPdo();
            
        // } catch (\Exception $e) {

        //     \Instantiation::set_default_connection();
        //     return back()->withInput()->with('error', 'Conexión fallida, revise los parámetros de configuración de la base de datos.');

        // }

        // \Instantiation::set_default_connection();
        
        $empresaColaborativa->save();

        return redirect()->route('admin.empresasColaborativas.manage')->with('success', 'Empresa actualizada con éxito.');
    }

    public function manage()
    {

        $empresaColaborativa = EmpresaColaborativa::all();

        return view('empresasColaborativas.manage', ['empresaColaborativa' => $empresaColaborativa]);
    }

    public function delete($id)
    {
        $empresaColaborativa = EmpresaColaborativa::where('id', $id)->first();
        return view('empresasColaborativas.delete', ['empresaColaborativa' => $empresaColaborativa]);
    }

    public function remove(Request $request)
    {
        $empresaColaborativa = EmpreEmpresaColaborativasa::where('id', $request->id)->first();

        $request->validate([
            'name' => 'in:' . $empresaColaborativa->name
        ]);

        // DB::statement("DROP DATABASE `{$empresa->database}`");

        $empresaColaborativa->delete();

        return redirect()->route('admin.empresasColaborativas.manage')->with('success', 'Empresa eliminada con éxito.');
    }

}
