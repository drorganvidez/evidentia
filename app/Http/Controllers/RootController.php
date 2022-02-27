<?php

namespace App\Http\Controllers;

use App\Models\Instance;
use Illuminate\Http\Request;

class RootController extends Controller
{
    public function root()
    {
        $instance = Instance::orderBy('created_at', 'desc')->first();

        if($instance == null){
            return redirect()->route('admin.login');
        }

        return redirect()->route('instance.login',['instance' => $instance->route]);
    }
}
