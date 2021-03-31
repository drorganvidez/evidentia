<?php

namespace App\Http\Controllers;

use App\Models\Instance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuickInstances extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:LECTURE');
    }

    public function list()
    {
        $instance = \Instantiation::instance();
        \Instantiation::set_default_connection();
        $instances = Instance::orderBy('created_at', 'desc')->get();
        $route = route('lecture.instances.save',$instance);

        return view('manage.instances', ['instances' => $instances, 'instance' => $instance, 'route' => $route ]);
    }

    public function save(Request $request)
    {

        // nos conectamos a la base de meta administración de Evidentia
        \Instantiation::set_default_connection();

        // validación
        $request->validate([
            'name' => 'required|unique:instances'
        ]);

        // creamos la ruta a través del nombre del curso, si no sigue el patrón, volvemos al form
        $name = $request->input('name');
        $route = "";
        try {
            $route = explode('/',$name)[0];
            $route = explode(' ',$route)[1];
        }catch(\Exception $e){
            return back()->withInput()->with('error', 'El formato para el nombre del curso no es correcto: ' . $e->getMessage());
        }

        // fecha preformateada para usarlo en el nombre de la base de datos
        $fecha = Carbon::now();
        $fecha = str_replace(" ", "_", $fecha);
        $fecha = str_replace("-", "_", $fecha);
        $fecha = str_replace(":", "_", $fecha);

        // creación de nueva instancia
        $instance = new Instance();
        $instance->name = $name;
        $instance->route = $route;
        $instance->host = 'localhost';
        $instance->port = '33060';
        $instance->database = 'evidentia_' . $fecha;
        $instance->username = 'homestead';
        $instance->password = 'secret';
        $instance->active = true;

        DB::connection()->getPdo()->exec("CREATE DATABASE `{$instance->database}`");
        DB::connection()->getPdo()->exec("ALTER SCHEMA `{$instance->database}`  DEFAULT CHARACTER SET utf8mb4  DEFAULT COLLATE utf8mb4_unicode_ci");


        \Instantiation::set($instance);

        try {

            DB::connection()->getPdo();
            config(['database.connections.quickinstance' => [
                'driver' => 'mysql',
                'host' => $instance->host,
                'database' => $instance->database,
                'port' => $instance->port,
                'username' => $instance->username,
                'password' => $instance->password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'engine' => 'InnoDB',
            ]]);

            Artisan::call('migrate',
                [
                    '--path' => 'database/migrations/instances',
                    '--database' => 'quickinstance'
                ]);

            Artisan::call('db:seed',
                [
                    '--class' => 'SampleSeeder',
                    '--database' => 'quickinstance'
                ]);
        } catch (\Exception $e) {

            \Instantiation::set_default_connection();
            DB::statement("DROP DATABASE `{$instance->database}`");
            return back()->withInput()->with('error', 'Conexión fallida, contacte con el administrador del servidor: ' . $e->getMessage());

        }

        \Instantiation::set_default_connection();

        $instance->save();

        $message = 'Instancia creada con éxito.<br><br>CREDENCIALES DE ACCESO</br>Usuario: <code style="color: white">profesor1</code><br>Contraseña: <code style="color: white">profesor1</code>';

        return redirect()->route('lecture.instances.list',\Instantiation::instance())->with('success', $message);

    }
}
