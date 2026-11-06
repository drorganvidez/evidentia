<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Enums\Deadline;
use App\Facades\Activity;
use App\Http\Middleware\CheckRoles;
use App\Models\Configuration;
use App\Services\Deadlines;
use App\Support\Changes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    // los cinco plazos, a la hora
    private function deadlines(Configuration $configuration): array
    {
        $deadlines = [];
        foreach (Deadline::cases() as $deadline) {
            $value = $configuration->{$deadline->column()};
            $deadlines[$deadline->column()] = $value === null ? null : Carbon::parse($value)->format('Y-m-d H:i');
        }

        return $deadlines;
    }

    public function config_save(Request $request)
    {

        // un plazo puede estar ya cerrado: así se guarda la configuración sin tener que reabrirlo
        $request->validate([
            'upload_evidences_date' => 'required|date_format:Y-m-d',
            'validate_evidences_date' => 'required|date_format:Y-m-d|after_or_equal:upload_evidences_date',
            'meetings_date' => 'required|date_format:Y-m-d',
            'bonus_date' => 'required|date_format:Y-m-d',
            'attendees_date' => 'required|date_format:Y-m-d',
            'upload_evidences_time' => 'required|date_format:H:i',
            'validate_evidences_time' => 'required|date_format:H:i',
            'meetings_time' => 'required|date_format:H:i',
            'bonus_time' => 'required|date_format:H:i',
            'attendees_time' => 'required|date_format:H:i',
        ]);

        $configuration = Configuration::find(1);

        if (! $configuration) {
            $configuration = new Configuration;
            $configuration->id = 1;
        }

        $before = $this->deadlines($configuration);

        DB::transaction(function () use ($request, $configuration, $before) {
            $configuration->upload_evidences_timestamp = $request->input('upload_evidences_date').' '.$request->input('upload_evidences_time');
            $configuration->validate_evidences_timestamp = $request->input('validate_evidences_date').' '.$request->input('validate_evidences_time');
            $configuration->meetings_timestamp = $request->input('meetings_date').' '.$request->input('meetings_time');
            $configuration->bonus_timestamp = $request->input('bonus_date').' '.$request->input('bonus_time');
            $configuration->attendee_timestamp = $request->input('attendees_date').' '.$request->input('attendees_time');

            $configuration->save();

            // solo los plazos que cambian
            Activity::record(
                ActivityAction::ConfigChanged,
                $configuration,
                details: Changes::between($before, $this->deadlines($configuration)),
                subjectTitle: 'Configuración del curso',
            );
        });

        app(Deadlines::class)->refresh();

        if (Auth::user()->hasRole('LECTURE')) {
            return redirect()->route('lecture.config')->with('success', 'Configuración guardada con éxito.');
        } else {
            return redirect()->route('president.config')->with('success', 'Configuración guardada con éxito.');
        }

    }
}
