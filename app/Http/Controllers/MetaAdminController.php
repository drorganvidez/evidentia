<?php

namespace App\Http\Controllers;

use App\Instance;
use Illuminate\Http\Request;

class MetaAdminController extends Controller
{
    public function admin()
    {
        return view('instances.admin');
    }

    public function list()
    {

        $instances = Instance::all();

        return view('instances.list', ['instances' => $instances]);
    }
}
