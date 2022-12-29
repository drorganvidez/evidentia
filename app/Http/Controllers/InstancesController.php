<?php

namespace App\Http\Controllers;

use App\Models\Instance;
use Illuminate\Http\Request;

class InstancesController extends Controller
{
    public function list()
    {

        $instances = Instance::all();

        return view('instances.list', ['instances' => $instances]);
    }
}
