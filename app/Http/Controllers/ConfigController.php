<?php

namespace App\Http\Controllers;

use App\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:LECTURE');
    }


    public function config()
    {
        $instance = \Instantiation::instance();
        $route = route('lecture.config.save',$instance);
        $configuration = Configuration::find(1);

        return view('config.config',
            ['instance' => $instance, 'route' => $route, 'configuration' => $configuration]);
    }

    public function config_save(Request $request)
    {
        $instance = \Instantiation::instance();
        $route = route('lecture.config.save',$instance);

        $validatedData = $request->validate([
            'upload_evidences_date' => 'required|date_format:Y-m-d|after:today',
            'validate_evidences_date' => 'required|date_format:Y-m-d|after:today|after:upload_evidences_date',
            'meetings_date' => 'required|date_format:Y-m-d|after:today',
            'bonus_date' => 'required|date_format:Y-m-d|after:today',
        ]);

        $configuration = Configuration::find(1);

        $configuration->upload_evidences_timestamp = $request->input('upload_evidences_date')." ".$request->input('upload_evidences_time');
        $configuration->validate_evidences_timestamp = $request->input('validate_evidences_date')." ".$request->input('validate_evidences_time');
        $configuration->meetings_timestamp = $request->input('meetings_date')." ".$request->input('meetings_time');
        $configuration->bonus_timestamp = $request->input('bonus_date')." ".$request->input('bonus_time');

        $configuration->save();

        return redirect()->route('lecture.config',$instance)->with('success','Configuración guardada con éxito.');

    }
}
