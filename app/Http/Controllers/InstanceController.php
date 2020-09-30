<?php

namespace App\Http\Controllers;

use App\Instance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InstanceController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {

        return view('instances.createandedit', ['route' => route('admin.instance.new')]);
    }

    public function new(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|unique:instances',
            'route' => 'required|unique:instances',
            'host' => 'required',
            'port' => 'required',
            'database' => 'required|unique:instances',
            'username' => 'required',
            'password' => 'required',
        ]);

        $instance = new Instance();

        $instance->name = $request->name;
        $instance->route = $request->route;
        $instance->host = $request->host;
        $instance->port = $request->port;
        $instance->database = $request->database;
        $instance->username = $request->username;
        $instance->password = $request->password;
        $instance->active = true;

        DB::connection()->getPdo()->exec("CREATE DATABASE IF NOT EXISTS `{$instance->database}`");
        DB::connection()->getPdo()->exec("ALTER SCHEMA `{$instance->database}`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");

        \Instantiation::set($instance);

        try {
            DB::connection()->getPdo();
            \Instantiation::migrate();
            Artisan::call('db:seed',
                [
                    '--class' => 'SampleSeeder',
                    '--database' => 'instance'
                ]);
        } catch (\Exception $e) {

            \Instantiation::set_default_connection();
            DB::statement("DROP DATABASE `{$instance->database}`");
            return back()->withInput()->with('error', 'Conexión fallida, revise los parámetros de configuración de la base de datos.')->setStatusCode(422);

        }

        \Instantiation::set_default_connection();

        $instance->save();

        $message = 'Instancia creada con éxito.<br><br>CREDENCIALES DE ACCESO</br>Usuario: <code style="color: white">profesor1</code><br>Contraseña: <code style="color: white">profesor1</code>';

        return redirect()->route('admin.instance.manage')->with('success', $message)->setStatusCode(200);
    }

    public function edit($id)
    {
        $instance = Instance::find($id);
        return view('instances.createandedit', ['instance' => $instance, 'edit' => true, 'route' => route('admin.instance.manage.save')]);
    }

    public function save(Request $request)
    {

        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('instances')->ignore($request->_id)],
            'route' => ['required', Rule::unique('instances')->ignore($request->_id)],
            'host' => 'required',
            'port' => 'required',
            'username' => 'required',
            'password' => 'required',
        ]);

        $instance = Instance::where('id', $request->_id)->first();
        $instance->name = $request->name;
        $instance->route = $request->route;
        $instance->host = $request->host;
        $instance->port = $request->port;
        $instance->username = $request->username;
        $instance->password = $request->password;

        \Instantiation::set($instance);

        try {
            DB::connection()->getPdo();
            $instance->save();
        } catch (\Exception $e) {

            \Instantiation::set_default_connection();
            return back()->withInput()->with('error', 'Conexión fallida, revise los parámetros de configuración de la base de datos.');

        }

        \Instantiation::set_default_connection();

        return redirect()->route('admin.instance.manage')->with('success', 'Instancia actualizada con éxito.');
    }

    public function manage()
    {

        $instances = Instance::all();

        return view('instances.manage', ['instances' => $instances]);
        return "hola";
    }

    public function delete($id)
    {
        $instance = Instance::where('id', $id)->first();
        return view('instances.delete', ['instance' => $instance]);
    }

    public function remove(Request $request)
    {
        $instance = Instance::where('id', $request->id)->first();

        $request->validate([
            'name' => 'in:' . $instance->name
        ]);

        DB::statement("DROP DATABASE `{$instance->database}`");

        $instance->delete();

        return redirect()->route('admin.instance.manage')->with('success', 'Instancia eliminada con éxito.');
    }

}
