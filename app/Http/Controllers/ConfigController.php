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
use Illuminate\Validation\Rule;

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
        $close = route(Auth::user()->hasRole('LECTURE') ? 'lecture.config.close' : 'president.config.close');

        return view('config.config',
            ['route' => $route, 'close' => $close, 'configuration' => $configuration]);
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

    // el evento, los plazos y los límites de horas, para anotar solo lo que cambia
    private function settings(Configuration $configuration): array
    {
        $limit = fn ($value): ?float => $value === null ? null : (float) $value;

        return [
            'event_name' => $configuration->event_name,
            'event_edition' => $configuration->event_edition,
        ] + $this->deadlines($configuration) + [
            'max_evidences_hours' => $limit($configuration->max_evidences_hours),
            'max_attendees_hours' => $limit($configuration->max_attendees_hours),
        ];
    }

    // cierra un plazo abierto en este minuto; cerrar la validación cierra también la subida
    public function close(Request $request)
    {
        $request->validate(['deadline' => ['required', Rule::enum(Deadline::class)]]);

        $deadline = Deadline::from($request->input('deadline'));
        $configuration = Configuration::findOrFail(1);
        $now = now()->startOfMinute();
        $closing = $deadline === Deadline::ValidateEvidences ? [Deadline::UploadEvidences, $deadline] : [$deadline];
        $before = $this->settings($configuration);

        DB::transaction(function () use ($configuration, $closing, $now, $before) {
            foreach ($closing as $each) {
                $value = $configuration->{$each->column()};
                if ($value === null || Carbon::parse($value)->isAfter($now)) {
                    $configuration->{$each->column()} = $now->format('Y-m-d H:i:s');
                }
            }
            $configuration->save();

            Activity::record(
                ActivityAction::ConfigChanged,
                $configuration,
                details: Changes::between($before, $this->deadlines($configuration)),
                subjectTitle: 'Configuración del curso',
            );
        });

        app(Deadlines::class)->refresh();

        return redirect()->route(Auth::user()->hasRole('LECTURE') ? 'lecture.config' : 'president.config')
            ->with('success', 'Plazo de '.mb_strtolower($deadline->label()).' cerrado.');
    }

    public function config_save(Request $request)
    {

        // un plazo puede estar ya cerrado: así se guarda la configuración sin tener que reabrirlo
        $request->validate([
            'event_name' => 'required|string|max:120',
            'event_edition' => 'nullable|string|max:20',
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
            'max_evidences_hours' => 'nullable|numeric|min:0|max:9999',
            'max_attendees_hours' => 'nullable|numeric|min:0|max:9999',
        ], [], ['event_name' => 'nombre del evento', 'event_edition' => 'edición', 'max_evidences_hours' => 'límite de horas de evidencias', 'max_attendees_hours' => 'límite de horas de asistencia']);

        $configuration = Configuration::find(1);

        if (! $configuration) {
            $configuration = new Configuration;
            $configuration->id = 1;
        }

        $before = $this->settings($configuration);

        DB::transaction(function () use ($request, $configuration, $before) {
            $configuration->event_name = trim($request->input('event_name'));
            $configuration->event_edition = $request->filled('event_edition') ? trim($request->input('event_edition')) : null;
            $configuration->upload_evidences_timestamp = $request->input('upload_evidences_date').' '.$request->input('upload_evidences_time');
            $configuration->validate_evidences_timestamp = $request->input('validate_evidences_date').' '.$request->input('validate_evidences_time');
            $configuration->meetings_timestamp = $request->input('meetings_date').' '.$request->input('meetings_time');
            $configuration->bonus_timestamp = $request->input('bonus_date').' '.$request->input('bonus_time');
            $configuration->attendee_timestamp = $request->input('attendees_date').' '.$request->input('attendees_time');
            $configuration->max_evidences_hours = $request->filled('max_evidences_hours') ? $request->input('max_evidences_hours') : null;
            $configuration->max_attendees_hours = $request->filled('max_attendees_hours') ? $request->input('max_attendees_hours') : null;

            $configuration->save();

            // solo lo que cambia
            Activity::record(
                ActivityAction::ConfigChanged,
                $configuration,
                details: Changes::between($before, $this->settings($configuration)),
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
