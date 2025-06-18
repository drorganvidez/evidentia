<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckRoles;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':LECTURE,PRESIDENT');
    }

    public function config()
    {

        $route = null;
        if (Auth::user()->hasRole('LECTURE')) {
            $route = route('lecture.config.save');
        } else {
            $route = route('president.config.save');
        }

        $configuration = Configuration::find(1);

        return view('config.config',
            ['route' => $route, 'configuration' => $configuration]);
    }

    public function config_save(Request $request)
    {

        $request->validate([
            'upload_evidences_date' => 'required|date_format:Y-m-d|after:today',
            'validate_evidences_date' => 'required|date_format:Y-m-d|after:today|after:upload_evidences_date',
            'meetings_date' => 'required|date_format:Y-m-d|after:today',
            'bonus_date' => 'required|date_format:Y-m-d|after:today',
            'attendees_date' => 'required|date_format:Y-m-d|after:today',
        ]);

        $configuration = Configuration::find(1);

        $configuration->upload_evidences_timestamp = $request->input('upload_evidences_date').' '.$request->input('upload_evidences_time');
        $configuration->validate_evidences_timestamp = $request->input('validate_evidences_date').' '.$request->input('validate_evidences_time');
        $configuration->meetings_timestamp = $request->input('meetings_date').' '.$request->input('meetings_time');
        $configuration->bonus_timestamp = $request->input('bonus_date').' '.$request->input('bonus_time');
        $configuration->attendee_timestamp = $request->input('attendees_date').' '.$request->input('attendees_time');

        $configuration->save();

        if (Auth::user()->hasRole('LECTURE')) {
            return redirect()->route('lecture.config')->with('success', 'Configuración guardada con éxito.');
        } else {
            return redirect()->route('president.config')->with('success', 'Configuración guardada con éxito.');
        }

    }
}
