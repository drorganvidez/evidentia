<?php

namespace App\Http\Controllers;

use App\Instance;
use Illuminate\Http\Request;

class InstanceController extends Controller
{
    public function list(){

        $instances = Instance::all();

        return view('instances.list', ['instances' => $instances]);
    }
}
