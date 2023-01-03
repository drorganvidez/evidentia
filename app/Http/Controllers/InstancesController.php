<?php

namespace App\Http\Controllers;

use App\Http\Services\InstanceService;
use App\Models\Instance;
use Illuminate\Http\Request;

class InstancesController extends Controller
{

    public InstanceService $instance_service;

    public function __construct()
    {
        $this->instance_service = new InstanceService();
    }

    public function list()
    {

        $instances = Instance::all();

        return view('instances.list', ['instances' => $instances]);
    }

    public function create()
    {
        return view('instances.createandedit', [
            'action' => 'admin.instances.create_p'
        ]);
    }

    public function create_p(Request $request)
    {
        $this->instance_service->validate();

        $data = [
            'name' => $request->name,
            'route' => $request->route,
            'host' => $request->host,
            'port' => $request->port,
            'database' => $request->database,
            'username' => $request->username,
            'password' => $request->password,
            'active' => true
        ];

        $entity_resource = $this->instance_service->create($data);
        $instance = $this->instance_service->entity($entity_resource);

        $this->instance_service->instance_database($instance);

        $request->session()->flash('instance', $instance);

        return redirect()->route('admin.instances.list')->with('success', 'Instancia creada con éxito');

    }

    public function edit($id)
    {
        $instance = Instance::findOrFail($id);

        return view('instances.createandedit', [
            'action' => 'admin.instances.edit_p',
            'item' => $instance
        ]);
    }

    public function edit_p(Request $request)
    {
        $this->instance_service->validate_except(['route']);

        $id = $request->input('_id');

        $data = [
            'name' => $request->name,
            'host' => $request->host,
            'port' => $request->port,
            'database' => $request->database,
            'username' => $request->username,
            'password' => $request->password
        ];

        $this->instance_service->update($id, $data);

        return redirect()->route('admin.instances.list')->with('success', 'Instancia editada con éxito');
    }

    public function delete(Request $request)
    {
        $id = $request->input("_id");
        $instance = $this->instance_service->find($id);

        $this->instance_service->delete_database($instance);
        $this->instance_service->delete_instance_files($instance);

        $this->instance_service->delete($id);

        return redirect()->route('admin.instances.list')->with('success', 'Instancia borrada con éxito');
    }

}
