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

        DB::statement("CREATE DATABASE `{$instance->database}`");

        $this->set($instance);

        try {
            DB::connection()->getPdo();
            Artisan::call('migrate',
                [
                    '--path' => 'database/migrations/instances',
                    '--database' => 'instance'
                ]);
        } catch (\Exception $e) {

            $this->reset();
            DB::statement("DROP DATABASE `{$instance->database}`");
            return back()->withInput()->with('error', 'Conexión fallida, revise los parámetros de configuración de la base de datos.');

        }

        $this->reset();

        $instance->save();

        return redirect()->route('admin.instance.manage')->with('success', 'Instancia creada con éxito.');
    }

    private function set($instance)
    {
        config(['database.connections.instance' => [
            'driver' => 'mysql',
            'host' => $instance->host,
            'database' => $instance->database,
            'port' => $instance->port,
            'username' => $instance->username,
            'password' => $instance->password
        ]]);
        config(['database.default' => 'instance']);
    }

    private function reset()
    {
        Artisan::call('config:clear');
        config(['database.default' => 'mysql']);
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

        $this->set($instance);

        try {
            DB::connection()->getPdo();
            $instance->save();
        } catch (\Exception $e) {

            $this->reset();
            return back()->withInput()->with('error', 'Conexión fallida, revise los parámetros de configuración de la base de datos.');

        }

        $this->reset();

        return redirect()->route('admin.instance.manage')->with('success', 'Instancia actualizada con éxito.');
    }

    public function manage()
    {
        $instances = Instance::all();
        return view('instances.manage', ['instances' => $instances]);
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

        return redirect()->route('admin.instance.manage')->with('success', 'Instancia eliminada con éxito.');;
    }

}
