<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Proof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LectureDashboardController extends Controller {


    public function view($instace){

        $instance = \Instantiation::instance();

        return view('dashboard.view',['instance'=> $instance]);
    }







}