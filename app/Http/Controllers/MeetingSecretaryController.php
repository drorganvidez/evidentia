<?php

namespace App\Http\Controllers;

use App\Exports\MeetingRequestExport;
use App\Exports\MeetingMinutesExport;
use App\Exports\SignaturesheetExport;
use App\Http\Services\UserService;
use App\Models\Agreement;
use App\Models\DefaultList;
use App\Models\Diary;
use App\Models\DiaryPoints;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Point;
use App\Models\SignatureSheet;
use App\Models\User;
use App\Rules\CheckHoursAndMinutes;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;


class MeetingSecretaryController extends Controller
{

    private $user_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:SECRETARY');
        $this->user_service = new UserService();
    }

    public function manage()
    {
        $instance = \Instantiation::instance();

        return view('meeting.manage', ['instance' => $instance]);
    }

    /*
     *  Requests
     */
    public function request_list()
    {

        $instance = \Instantiation::instance();

        $meeting_requests = Auth::user()->secretary->meeting_requests;

        return view('meeting.request_list', ["meeting_requests" => $meeting_requests, "instance" => $instance]);
    }

    public function request_create()
    {
        $instance = \Instantiation::instance();

        return view('meeting.request_createandedit', ['instance' => $instance]);
    }

    public function request_new(Request $request_http)
    {
        $instance = \Instantiation::instance();

        $validator = Validator::make($request_http->all(), [
            'title' => 'required|min:5|max:255',
            'place' => 'required|min:5|max:255',
            'date' => 'required|date_format:Y-m-d|after:yesterday',
            'time' => 'required',
            'type' => 'required|numeric|min:1|max:2',
            'modality' => 'required|numeric|min:1|max:3',
            'points_list' => 'required'
        ]);

        if ($validator->fails()) {
            $points = json_decode($request_http->input('points_list'), true);
            return back()->withErrors($validator)->withInput()->with([
                'error' => 'Hay errores en el formulario.',
                'points' => collect($points)
            ]);
        }

        $meeting_request = MeetingRequest::create([
            'title' => $request_http->input('title'),
            'place' => $request_http->input('place'),
            'datetime' => $request_http->input('date') . " " . $request_http->input('time'),
            'type' => $request_http->input('type'),
            'modality' => $request_http->input('modality'),
            'comittee_id' => Auth::user()->secretary->comittee->id,
            'secretary_id' => Auth::user()->secretary->id
        ]);

        $diary = Diary::create([
            "meeting_request_id" => $meeting_request->id
        ]);

        foreach (json_decode($request_http->input('points_list'), 1) as $key => $value) {

            DiaryPoints::create([
                "diary_id" => $diary->id,
                "point" => $value
            ]);

        }

        // Genera PDF de la convocatoria
        $pdf = PDF::loadView('meeting.request_template', ['meeting_request' => $meeting_request]);
        $content = $pdf->download()->getOriginalContent();
        Storage::put(\Instantiation::instance() . '/meeting_requests/meeting_request_' . $meeting_request->id . '.pdf', $content);

        // creamos una hoja de firmas (si el usuario lo ha querido así)
        if ($request_http->input('create_signature_sheet') == 'on') {
            SignatureSheet::create([
                'title' => 'Hoja de firmas de ' . $request_http->input('title'),
                'random_identifier' => $this->generate_random_identifier_for_signature(4),
                'meeting_request_id' => $meeting_request->id,
                'secretary_id' => Auth::user()->secretary->id
            ]);
        }

        return redirect()->route('secretary.meeting.manage.request.list', $instance)->with('success', 'Convocatoria de reunión creada con éxito.');
    }

    public function request_edit($instance, $id)
    {
        $instance = \Instantiation::instance();

        $meeting_request = MeetingRequest::findOrFail($id);

        $points_array = array();

        foreach ($meeting_request->diary->diary_points as $point) {
            array_push($points_array, $point->point);
        }

        return view('meeting.request_createandedit', [
            'instance' => $instance,
            'meeting_request' => $meeting_request,
            'edit' => true,
            'points' => collect($points_array)
        ]);
    }

    public function request_save(Request $request_http)
    {
        $instance = \Instantiation::instance();

        $validator = Validator::make($request_http->all(), [
            'title' => 'required|min:5|max:255',
            'place' => 'required|min:5|max:255',
            'date' => 'required|date_format:Y-m-d|after:yesterday',
            'time' => 'required',
            'type' => 'required|numeric|min:1|max:2',
            'modality' => 'required|numeric|min:1|max:3',
            'points_list' => 'required'
        ]);

        if ($validator->fails()) {
            $points = json_decode($request_http->input('points_list'), true);
            return back()->withErrors($validator)->withInput()->with([
                'error' => 'Hay errores en el formulario.',
                'points' => collect($points)
            ]);
        }

        $meeting_request = MeetingRequest::findOrFail($request_http->input('_id'));

        $meeting_request->title = $request_http->input('title');
        $meeting_request->place = $request_http->input('place');
        $meeting_request->datetime = $request_http->input('date') . " " . $request_http->input('time');
        $meeting_request->modality = $request_http->input('modality');
        $meeting_request->type = $request_http->input('type');

        $meeting_request->save();

        // borramos los puntos del día anteriores
        $old_diary = $meeting_request->diary;
        $old_diary?->delete();

        // añadimos los nuevos puntos
        $diary = Diary::create([
            "meeting_request_id" => $meeting_request->id
        ]);

        foreach (json_decode($request_http->input('points_list'), 1) as $key => $value) {

            $diary->diary_points()->create([
                'diary_id' => $diary->id,
                'point' => $value
            ]);

        }

        /*
         *  Por alguna extraña razón de Laravel, no es capaz de remapear el objeto meeting_request, pese a que
         *  se ha modificado correctamente y las referencias de las entidades que apuntan a él son correctas
         *  en la base de datos. Hay que volver a traerse de la base la entidad mediante el id (obviamente, el id
         *  sigue siendo el mismo) y ya es correcta la asociación.
         *
         *  No sé, es muy extraño.
         */
        $meeting_request = MeetingRequest::findOrFail($request_http->input('_id'));

        // borramos el PDF de la convocatoria anterior
        Storage::delete(\Instantiation::instance() . '/meeting_requests/meeting_request_' . $meeting_request->id . '.pdf');

        // Genera PDF de la convocatoria
        $pdf = PDF::loadView('meeting.request_template', ['meeting_request' => $meeting_request]);
        $content = $pdf->download()->getOriginalContent();
        Storage::put(\Instantiation::instance() . '/meeting_requests/meeting_request_' . $meeting_request->id . '.pdf', $content);

        return redirect()->route('secretary.meeting.manage.request.list', $instance)->with('success', 'Convocatoria de reunión editada con éxito.');
    }

    public function request_download($instance, $id)
    {
        $meeting_request = MeetingRequest::findOrFail($id);

        $response = Storage::download(\Instantiation::instance() . '/meeting_requests/meeting_request_' . $meeting_request->id . '.pdf');

        // limpiar búfer de salida
        ob_end_clean();

        return $response;
    }

    public function request_remove(Request $request)
    {
        $meeting_request = MeetingRequest::where('id', $request->input('meeting_request_id'))->first();

        $instance = \Instantiation::instance();

        // borramos el pdf del acta antigua
        Storage::delete(\Instantiation::instance() . '/meeting_requests/meeting_request_' . $meeting_request->id . '.pdf');

        // desemparejamos signature sheet (si la hubiera)
        $signature_sheet = $meeting_request->signature_sheet;
        if ($signature_sheet != null) {
            $signature_sheet->meeting_request_id = null;
            $signature_sheet->save();
        }

        // eliminamos la entidad en sí
        $meeting_request->delete();

        return redirect()->route('secretary.meeting.manage.request.list', $instance)->with('success', 'Convocatoria eliminada con éxito.');
    }

    /*
     *  Signature sheets
     */
    public function signaturesheet_list()
    {
        $instance = \Instantiation::instance();

        $signature_sheets = Auth::user()->secretary->signature_sheets;

        return view('meeting.signaturesheet_list', ["instance" => $instance, 'signature_sheets' => $signature_sheets]);
    }

    public function signaturesheet_create()
    {
        $instance = \Instantiation::instance();

        $available_meeting_requests = Auth::user()->secretary->meeting_requests;

        $available_meeting_requests = $available_meeting_requests->filter(function ($value, $key) {
            return $value->signature_sheet == null;
        });

        return view('meeting.signaturesheet_create', ['instance' => $instance, 'available_meeting_requests' => $available_meeting_requests]);
    }

    public function signaturesheet_new(Request $request)
    {
        $instance = \Instantiation::instance();

        $request->validate([
            'title' => 'required|min:5|max:255'
        ]);

        SignatureSheet::create([
            'title' => $request->input('title'),
            'random_identifier' => $this->generate_random_identifier_for_signature(4),
            'meeting_request_id' => $request->input('meeting_request'),
            'secretary_id' => Auth::user()->secretary->id
        ]);

        return redirect()->route('secretary.meeting.manage.signaturesheet.list', $instance)->with('success', 'Hoja de firmas creada con éxito.');

    }

    private function generate_random_identifier_for_signature($number): string
    {

        // generdor de identificador aleatorio (comprueba si ya está ocupado)

        $random_identifier = \Random::getRandomIdentifier($number);
        $signature_sheet_with_random_identifier = SignatureSheet::where('random_identifier', $random_identifier)->first();

        if ($signature_sheet_with_random_identifier != null) {
            return $this->generate_random_identifier_for_signature($number);
        }

        return $random_identifier;
    }

    public function signaturesheet_view($instance, $signature_sheet)
    {
        $signature_sheet = SignatureSheet::findOrFail($signature_sheet);

        return view('meeting.signaturesheet_view', ["instance" => $instance, 'signature_sheet' => $signature_sheet]);
    }

    public function signaturesheet_edit($instance, $id)
    {
        $instance = \Instantiation::instance();

        $signature_sheet = SignatureSheet::findOrFail($id);

        $available_meeting_requests = Auth::user()->secretary->meeting_requests;

        $available_meeting_requests = $available_meeting_requests->filter(function ($value, $key) {
            return $value->signature_sheet == null;
        });

        return view('meeting.signaturesheet_edit', [
            'instance' => $instance,
            'signature_sheet' => $signature_sheet,
            'available_meeting_requests' => $available_meeting_requests,
            'edit' => true
        ]);
    }

    public function signaturesheet_save(Request $request)
    {
        $instance = \Instantiation::instance();

        $signature_sheet = SignatureSheet::findOrFail($request->input('_id'));

        $request->validate([
            'title' => 'required|min:5|max:255'
        ]);

        // actualizamos el título
        $signature_sheet->title = $request->input('title');
        $signature_sheet->save();

        // actualizamos la convocatoria asociada
        $meeting_request = MeetingRequest::find($request->input('meeting_request_id'));

        if ($meeting_request != null) {
            if ($meeting_request->signature_sheet == null) {
                $signature_sheet->meeting_request_id = $meeting_request->id;
                $signature_sheet->save();
            }
        }

        return redirect()->route('secretary.meeting.manage.signaturesheet.list', $instance)->with('success', 'Hoja de firmas actualizada con éxito.');

    }


    public function signaturesheet_remove(Request $request)
    {
        $signature_sheet = SignatureSheet::where('id', $request->input('signature_sheet_id'))->first();

        $instance = \Instantiation::instance();

        // eliminamos la entidad en sí
        $signature_sheet->delete();

        return redirect()->route('secretary.meeting.manage.signaturesheet.list', $instance)->with('success', 'Hoja de firmas eliminada con éxito.');
    }

    /*
     *  Minutes
     */
    public function minutes_list()
    {
        $instance = \Instantiation::instance();

        $meeting_minutes = Auth::user()->secretary->meeting_minutes;

        return view('meeting.minutes_list', ["instance" => $instance, 'meeting_minutes' => $meeting_minutes]);
    }

    public function minutes_create()
    {
        $instance = \Instantiation::instance();

        return redirect()->route('secretary.meeting.manage.minutes.create.step1', ['instance' => $instance]);
    }

    public function minutes_create_step1()
    {
        $instance = \Instantiation::instance();

        $meeting_requests = Auth::user()->secretary->meeting_requests;

        return view('meeting.minutes_create_step1', [
            'instance' => $instance,
            'meeting_requests' => $meeting_requests
        ]);
    }

    public function minutes_create_step1_p(Request $request)
    {
        $instance = \Instantiation::instance();

        $meeting_request = MeetingRequest::find($request->input('meeting_request'));

        return redirect()->route('secretary.meeting.manage.minutes.create.step2', [
            'instance' => $instance,
            'meeting_request' => $meeting_request
        ]);
    }

    public function minutes_create_step2(Request $request)
    {
        $instance = \Instantiation::instance();

        $meeting_request = MeetingRequest::find($request->input('meeting_request'));

        $signature_sheets = Auth::user()->secretary->signature_sheets;

        return view('meeting.minutes_create_step2', [
            'instance' => $instance,
            'meeting_request' => $meeting_request,
            'signature_sheets' => $signature_sheets
        ]);
    }

    public function minutes_create_step2_p(Request $request)
    {
        $instance = \Instantiation::instance();

        $meeting_request_input = $request->input('meeting_request');
        $signature_sheet_input = $request->input('signature_sheet');

        $meeting_request = MeetingRequest::find($meeting_request_input);
        $signature_sheet = SignatureSheet::find($signature_sheet_input);

        if ($signature_sheet != null) {

            // si la hoja de firmas tiene una convocatoria asociada, se descarta cualquier otra elegida
            // por el secretario
            if ($signature_sheet->meeting_request != null) {
                $meeting_request = $signature_sheet->meeting_request;
            }

        }

        return redirect()->route('secretary.meeting.manage.minutes.create.step3', [
            'instance' => $instance,
            'meeting_request' => $meeting_request,
            'signature_sheet' => $signature_sheet
        ]);
    }

    public function minutes_create_step3(Request $request)
    {
        $instance = \Instantiation::instance();

        $meeting_request = MeetingRequest::find($request->input('meeting_request'));
        $signature_sheet = SignatureSheet::find($request->input('signature_sheet'));

        $users = $this->user_service->all_except_logged();
        $defaultlists = Auth::user()->secretary->default_lists;

        return view('meeting.minutes_create_step3', [
            'instance' => $instance,
            'meeting_request' => $meeting_request,
            'signature_sheet' => $signature_sheet,
            'users' => $users,
            'defaultlists' => $defaultlists
        ]);
    }

    public function minutes_create_step3_p(Request $request)
    {

        $instance = \Instantiation::instance();
        $minutes = $request->input('minutes');

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:5|max:255',
            'type' => 'required|numeric|min:1|max:2',
            'hours' => ['required_without:minutes', 'nullable', 'numeric', 'sometimes', 'max:99', new CheckHoursAndMinutes($request->input('minutes'))],
            'minutes' => ['required_without:hours', 'nullable', 'numeric', 'sometimes', 'max:60', new CheckHoursAndMinutes($request->input('hours'))],
            'place' => 'required|min:5|max:255',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required',
            'users' => 'required|array|min:1'
        ]);

        if ($validator->fails()) {
            $points = json_decode($request->input('points_json'), true);
            return back()->withErrors($validator)->withInput()->with([
                'error' => 'Hay errores en el formulario.',
                'points' => collect($points)
            ]);
        }

        $meeting = Meeting::create([
            'title' => $request->input('title'),
            'hours' => $request->input('hours') + floor(($minutes * 100) / 60) / 100,
            'type' => $request->input('type'),
            'modality' => $request->input('modality'),
            'place' => $request->input('place'),
            'datetime' => $request->input('date') . " " . $request->input('time')
        ]);

        $meeting->comittee()->associate(Auth::user()->secretary->comittee);

        $meeting->save();

        // Asociamos los usuarios a la reunión
        $users_ids = $request->input('users', []);
        foreach ($users_ids as $user_id) {

            $user = User::find($user_id);
            $meeting->users()->attach($user);

        }

        // Añadimos el secretario a la reunión
        $meeting->users()->attach(Auth::user()->secretary->user);

        // Guardamos los puntos y los acuerdos tomados
        $meeting_minutes = MeetingMinutes::create([
            'meeting_id' => $meeting->id,
            'secretary_id' => Auth::user()->secretary->id
        ]);

        $points = json_decode($request->input('points_json'), true);
        $points = collect($points);

        foreach ($points as $point) {
            $new_point = Point::create([
                'meeting_minutes_id' => $meeting_minutes->id,
                'title' => $point['title'],
                'duration' => $point['duration'] == '' ? 0 : $point['duration'],
                'description' => $point['description']
            ]);

            foreach ($point['agreements'] as $agreement) {

                $new_agreement = Agreement::create([
                    'point_id' => $new_point->id,
                    'description' => $agreement['description']
                ]);

                // generamos el identificador único para este acuerdo
                $identificator = "ISD";
                $identificator .= '-';
                $identificator .= Carbon::now()->format('Y-m-d');
                $identificator .= '-';
                $identificator .= Auth::user()->secretary->comittee->id;
                $identificator .= '-';
                $identificator .= $meeting->id;
                $identificator .= '-';
                $identificator .= $new_point->id;
                $identificator .= '-';
                $identificator .= $new_agreement->id;

                $new_agreement->identificator = $identificator;
                $new_agreement->save();
            }
        }

        // Genera PDF del acta
        $pdf = PDF::loadView('meeting.minutes_template', ['meeting_minutes' => $meeting_minutes]);
        $content = $pdf->download()->getOriginalContent();
        Storage::put(\Instantiation::instance() . '/meeting_minutes/meeting_minutes_' . $meeting_minutes->id . '.pdf', $content);

        return redirect()->route('secretary.meeting.manage.minutes.list', $instance)->with('success', 'Acta de reunión creada con éxito.');

    }

    public function minutes_edit($instance, $id)
    {
        $instance = \Instantiation::instance();

        $meeting_minutes = MeetingMinutes::findOrFail($id);

        $points_array = array();

        foreach ($meeting_minutes->points as $point) {

            $agreements_array = array();

            foreach ($point->agreements as $agreement) {
                $agreement_array = array(
                    'description' => $agreement->description
                );
                array_push($agreements_array, $agreement_array);
            }

            $point_array = array(
                'id' => $point->id,
                'title' => $point->title,
                'description' => $point->description,
                'duration' => $point->duration,
                'agreements' => $agreements_array
            );

            array_push($points_array, $point_array);
        }

        $points = collect($points_array);

        $users = $this->user_service->all_except_logged();
        $defaultlists = Auth::user()->secretary->default_lists;

        return view('meeting.minutes_edit', [
            'instance' => $instance,
            'meeting_minutes' => $meeting_minutes,
            'points' => $points,
            'users' => $users,
            'defaultlists' => $defaultlists
        ]);

    }

    public function minutes_save(Request $request)
    {

        $instance = \Instantiation::instance();
        $minutes = $request->input('minutes');

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:5|max:255',
            'type' => 'required|numeric|min:1|max:2',
            'hours' => ['required_without:minutes', 'nullable', 'numeric', 'sometimes', 'max:99', new CheckHoursAndMinutes($request->input('minutes'))],
            'minutes' => ['required_without:hours', 'nullable', 'numeric', 'sometimes', 'max:60', new CheckHoursAndMinutes($request->input('hours'))],
            'place' => 'required|min:5|max:255',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required',
            'users' => 'required|array|min:1'
        ]);

        if ($validator->fails()) {
            $points = json_decode($request->input('points_json'), true);
            return back()->withErrors($validator)->withInput()->with([
                'error' => 'Hay errores en el formulario.',
                'points' => collect($points)
            ]);
        }

        // modificamos la info básica de la reunión
        $meeting = Meeting::where('id', $request->input('meeting_id'))->first();
        $meeting->title = $request->input('title');
        $meeting->hours = $request->input('hours') + floor(($minutes * 100) / 60) / 100;
        $meeting->type = $request->input('type');
        $meeting->modality = $request->input('modality');
        $meeting->place = $request->input('place');
        $meeting->datetime = $request->input('date') . " " . $request->input('time');
        $meeting->save();

        // Asociamos los usuarios a la reunión
        $users_ids = $request->input('users', []);

        // eliminamos usuarios antiguos de la reunión
        foreach ($meeting->users as $user) {
            $meeting->users()->detach($user);
        }

        // agregamos los usuarios nuevos de la reunión
        foreach ($users_ids as $user_id) {
            $user = User::find($user_id);
            $meeting->users()->attach($user);
        }

        // borramos los puntos y acuerdos previos
        if ($meeting->meeting_minutes->points) {
            foreach ($meeting->meeting_minutes->points as $point) {
                foreach ($point->agreements as $agreement) {
                    $agreement->delete();
                }
                $point->delete();
            }
        }

        // borramos el pdf del acta antigua
        Storage::delete(\Instantiation::instance() . '/meeting_minutes/meeting_minutes_' . $meeting->meeting_minutes->id . '.pdf');

        // borramos el acta antigua
        $meeting->meeting_minutes->delete();

        // Añadimos el secretario a la reunión
        $meeting->users()->attach(Auth::user()->secretary->user);

        // Guardamos los puntos y los acuerdos tomados
        $meeting_minutes = MeetingMinutes::create([
            'meeting_id' => $meeting->id,
            'secretary_id' => Auth::user()->secretary->id
        ]);

        $points = json_decode($request->input('points_json'), true);
        $points = collect($points);

        foreach ($points as $point) {
            $new_point = Point::create([
                'meeting_minutes_id' => $meeting_minutes->id,
                'title' => $point['title'],
                'duration' => $point['duration'] == '' ? 0 : $point['duration'],
                'description' => $point['description']
            ]);

            foreach ($point['agreements'] as $agreement) {

                $new_agreement = Agreement::create([
                    'point_id' => $new_point->id,
                    'description' => $agreement['description']
                ]);

                // generamos el identificador único para este acuerdo
                $identificator = "ISD";
                $identificator .= '-';
                $identificator .= Carbon::now()->format('Y-m-d');
                $identificator .= '-';
                $identificator .= Auth::user()->secretary->comittee->id;
                $identificator .= '-';
                $identificator .= $meeting->id;
                $identificator .= '-';
                $identificator .= $new_point->id;
                $identificator .= '-';
                $identificator .= $new_agreement->id;

                $new_agreement->identificator = $identificator;
                $new_agreement->save();
            }
        }

        // Generamos de nuevo el PDF
        $pdf = PDF::loadView('meeting.minutes_template', ['meeting_minutes' => $meeting_minutes]);
        $content = $pdf->download()->getOriginalContent();
        Storage::put(\Instantiation::instance() . '/meeting_minutes/meeting_minutes_' . $meeting_minutes->id . '.pdf', $content);

        return redirect()->route('secretary.meeting.manage.minutes.list', $instance)->with('success', 'Acta de reunión editada con éxito.');
    }

    public function minutes_remove(Request $request)
    {
        $meeting_minutes = MeetingMinutes::where('id', $request->input('meeting_minutes_id'))->first();

        $instance = \Instantiation::instance();

        // borramos el pdf del acta antigua
        Storage::delete(\Instantiation::instance() . '/meeting_minutes/meeting_minutes_' . $meeting_minutes->id . '.pdf');

        $meeting_minutes->meeting->delete();
        $meeting_minutes->delete();

        return redirect()->route('secretary.meeting.manage.minutes.list', $instance)->with('success', 'Acta de reunión eliminada con éxito.');

    }

    public function minutes_download($instance, $id)
    {
        $meeting_minutes = MeetingMinutes::findOrFail($id);

        $response = Storage::download(\Instantiation::instance() . '/meeting_minutes/meeting_minutes_' . $meeting_minutes->id . '.pdf');

        // limpiar búfer de salida
        ob_end_clean();

        return $response;
    }

    public function meeting_requests_export($instance, $ext)
    {
        try {
            ob_end_clean();
            if(!in_array($ext, ['csv', 'pdf', 'xlsx'])){
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new MeetingRequestExport(), 'convocatorias-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function signaturesheet_export($instance, $ext)
    {
        try {
            ob_end_clean();
            if(!in_array($ext, ['csv', 'pdf', 'xlsx'])){
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new SignaturesheetExport(), 'firmas-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function meeting_minutes_export($instance, $ext)
    {
        try {
            ob_end_clean();
            if(!in_array($ext, ['csv', 'pdf', 'xlsx'])){
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new MeetingMinutesExport(), 'actas-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    /*
    public function list()
    {
        $instance = \Instantiation::instance();

        $meetings = Auth::user()->secretary->comittee->meetings()->get();

        return view('meeting.list',
            ['instance' => $instance, 'meetings' => $meetings]);
    }
    */

    /*
    public function create()
    {
        $instance = \Instantiation::instance();

        $users = User::orderBy('surname')->get();
        $defaultlists = Auth::user()->secretary->default_lists;

        return view('meeting.createandedit',
            ['instance' => $instance, 'users' => $users, 'defaultlists' => $defaultlists, 'route' => route('secretary.meeting.new',$instance)]);
    }
    */

    /*
    public function new(Request $request)
    {

        $instance = \Instantiation::instance();
        $minutes = $request->input('minutes');

        $validatedData = $request->validate([
            'title' => 'required|min:5|max:255',
            'type' => 'required|numeric|min:1|max:2',
            'hours' => ['required_without:minutes','nullable','numeric','sometimes','max:99',new CheckHoursAndMinutes($request->input('minutes'))],
            'minutes' => ['required_without:hours','nullable','numeric','sometimes','max:60',new CheckHoursAndMinutes($request->input('hours'))],
            'place' => 'required|min:5|max:255',
            'date' => 'required|date_format:Y-m-d|before:tomorrow',
            'time' => 'required',
            'users' => 'required|array|min:1'
        ]);

        $meeting = Meeting::create([
            'title' => $request->input('title'),
            'hours' => $request->input('hours') + floor(($minutes*100)/60)/100,
            'type' => $request->input('type'),
            'place' => $request->input('place'),
            'datetime' => $request->input('date')." ".$request->input('time')
        ]);

        $meeting->comittee()->associate(Auth::user()->secretary->comittee);

        $meeting->save();

        // Asociamos los usuarios a la reunión
        $users_ids = $request->input('users',[]);

        foreach($users_ids as $user_id)
        {

            $user = User::find($user_id);
            $meeting->users()->attach($user);

        }

        return redirect()->route('secretary.meeting.list',$instance)->with('success', 'Reunión creada con éxito.');

    }
    */

    /*
    public function edit($instance,$id)
    {
        $meeting = Meeting::find($id);
        $users = User::orderBy('surname')->get();
        $defaultlists = Auth::user()->secretary->default_lists;

        return view('meeting.createandedit',
            ['instance' => $instance, 'meeting' => $meeting, 'edit' => true, 'users' => $users, 'defaultlists' => $defaultlists, 'route' => route('secretary.meeting.save',$instance)]);
    }
    */

    public function defaultlist($instance, $id)
    {
        return DefaultList::find($id)->users;
    }

    /*
    public function save(Request $request)
    {

        $instance = \Instantiation::instance();
        $minutes = $request->input('minutes');

        $validatedData = $request->validate([
            'title' => 'required|min:5|max:255',
            'type' => 'required|numeric|min:1|max:2',
            'hours' => ['required_without:minutes','nullable','numeric','sometimes','max:99',new CheckHoursAndMinutes($request->input('minutes'))],
            'minutes' => ['required_without:hours','nullable','numeric','sometimes','max:60',new CheckHoursAndMinutes($request->input('hours'))],
            'place' => 'required|min:5|max:255',
            'date' => 'required|date_format:Y-m-d|before:tomorrow',
            'time' => 'required',
            'users' => 'required|array|min:1'
        ]);

        $meeting = Meeting::find($request->_id);
        $meeting->title = $request->input('title');
        $meeting->hours = $request->input('hours') + floor(($minutes*100)/60)/100;
        $meeting->type = $request->input('type');
        $meeting->place = $request->input('place');
        $meeting->datetime = $request->input('date')." ".$request->input('time');

        $meeting->save();

        // Asociamos los usuarios a la reunión
        $users_ids = $request->input('users',[]);

        // eliminamos usuarios antiguos de la reunión
        foreach($meeting->users as $user)
        {
            $meeting->users()->detach($user);
        }

        // agregamos los usuarios nuevos de la reunión
        foreach($users_ids as $user_id)
        {
            $user = User::find($user_id);
            $meeting->users()->attach($user);
        }

        return redirect()->route('secretary.meeting.list',$instance)->with('success', 'Reunión editada con éxito.');

    }
    */

    /*
    public function remove(Request $request)
    {
        $meeting = Meeting::find($request->_id);
        $instance = \Instantiation::instance();

        $meeting->delete();

        return redirect()->route('secretary.meeting.list',$instance)->with('success', 'Reunión eliminada con éxito.');
    }
    */
}
