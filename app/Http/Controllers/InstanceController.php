<?php

namespace App\Http\Controllers;

use App\Instance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class InstanceController extends Controller
{
    public function list(){

        $instances = Instance::all();

        return view('instances.list', ['instances' => $instances]);
    }

    public function admin(){
        return view('instances.admin');
    }

    public function create(){
        return view('instances.create');
    }

    public function new(Request $request){

        $request->validate([
            'name' => ['unique:instances'],
            'route' => ['unique:instances']
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

        config(['database.connections.instance' => [
            'driver'   => 'mysql',
            'host' => $instance->host,
            'database' => $instance->database,
            'port' => $instance->port,
            'username' => $instance->username,
            'password' => $instance->password
        ]]);

        config(['database.default' => 'instance']);

        Artisan::call('migrate',
                    [
                        '--path' => 'database/migrations/instances',
                        '--database' => 'instance'
                    ]);

        Artisan::call('config:clear');
        config(['database.default' => 'mysql']);

        $instance->save();

        return redirect("/");
    }
}
