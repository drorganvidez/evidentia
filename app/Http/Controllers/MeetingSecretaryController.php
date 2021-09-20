<?php

namespace App\Http\Controllers;

use App\Models\DefaultList;
use App\Models\Diary;
use App\Models\DiaryPoints;
use App\Models\Meeting;
use App\Models\MeetingRequest;
use App\Models\SignatureSheet;
use App\Rules\CheckHoursAndMinutes;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MeetingSecretaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:SECRETARY');
    }

    public function manage()
    {
        $instance = \Instantiation::instance();

        return view('meeting.manage',['instance' => $instance]);
    }

    public function request_list()
    {

        $instance = \Instantiation::instance();

        $meeting_requests = Auth::user()->secretary->meeting_requests;

        return view('meeting.request_list',["meeting_requests" => $meeting_requests, "instance" => $instance]);
    }

    public function request_create()
    {
        $instance = \Instantiation::instance();

        return view('meeting.request',['instance' => $instance]);
    }

    public function request_new(Request $request_http)
    {
        $instance = \Instantiation::instance();

        $request_http->validate([
            'title' => 'required|min:5|max:255',
            'place' => 'required|min:5|max:255',
            'date' => 'required|date_format:Y-m-d|after:yesterday',
            'time' => 'required',
            'type' => 'required|numeric|min:1|max:2',
            'modality' => 'required|numeric|min:1|max:3',
            'points_list' => 'required'
        ]);

        $meeting_request = MeetingRequest::create([
            'title' => $request_http->input('title'),
            'place' => $request_http->input('place'),
            'datetime' => $request_http->input('date')." ".$request_http->input('time'),
            'type' => $request_http->input('type'),
            'modality' => $request_http->input('modality'),
            'comittee_id' => Auth::user()->secretary->comittee->id,
            'secretary_id' => Auth::user()->secretary->id
        ]);

        $diary = Diary::create([
            "meeting_request_id" => $meeting_request->id
        ]);

        foreach (json_decode($request_http->input('points_list'),1) as $key => $value) {

            DiaryPoints::create([
                "diary_id" => $diary->id,
                "point" => $value
            ]);
        }

        // Genera PDF de la convocatoria
        $pdf = PDF::loadView('meeting.request_template', ['meeting_request' => $meeting_request]);
        $content = $pdf->download()->getOriginalContent();
        Storage::put(\Instantiation::instance() .'/meeting_requests/meeting_request_' .$meeting_request->id . '.pdf',$content) ;

        return redirect()->route('secretary.meeting.manage.request.list',$instance)->with('success', 'Convocatoria de reunión creada con éxito.');
    }

    public function request_download($instance, $id)
    {
        $meeting_request = MeetingRequest::findOrFail($id);

        $response = Storage::download(\Instantiation::instance() .'/meeting_requests/meeting_request_' .$meeting_request->id . '.pdf');

        // limpiar búfer de salida
        ob_end_clean();

        return $response;
    }

    public function signaturesheet_list()
    {
        $instance = \Instantiation::instance();

        $signature_sheets = Auth::user()->secretary->signature_sheets;

        return view('meeting.signaturesheet_list',["instance" => $instance, 'signature_sheets' => $signature_sheets]);
    }

    public function signaturesheet_create()
    {
        $instance = \Instantiation::instance();

        $available_meeting_requests = Auth::user()->secretary->meeting_requests;

        $available_meeting_requests = $available_meeting_requests->filter(function($value,$key){
            return $value->signature_sheet == null;
        });

        return view('meeting.signaturesheet_create',['instance' => $instance, 'available_meeting_requests' => $available_meeting_requests]);
    }

    private function generate_random_identifier_for_signature($number)
    {
        $random_identifier = \Random::getRandomIdentifier('4');
        $signature_sheet_with_random_identifier = SignatureSheet::where('random_identifier', $random_identifier)->first();

        if($signature_sheet_with_random_identifier != null){
            return $this->generate_random_identifier_for_signature($number);
        }

        return $random_identifier;
    }

    public function signaturesheet_new(Request $request)
    {
        $instance = \Instantiation::instance();

        $request->validate([
            'title' => 'required|min:5|max:255'
        ]);

        // generamos identificador aleatorio y comprobamos si ya está ocupado
        $random_identifier = \Random::getRandomIdentifier('4');
        $signature_sheet_with_random_identifier = SignatureSheet::where('random_identifier', $random_identifier)->first();
        if($signature_sheet_with_random_identifier != null){

        }

        $signature_sheet = SignatureSheet::create([
            'title' => $request->input('title'),
            'random_identifier' => $this->generate_random_identifier_for_signature(4),
            'meeting_request_id' => $request->input('meeting_request'),
            'secretary_id' => Auth::user()->secretary->id
        ]);

        return redirect()->route('secretary.meeting.manage.signaturesheet.list',$instance)->with('success', 'Reunión creada con éxito.');

    }

    public function list()
    {
        $instance = \Instantiation::instance();

        $meetings = Auth::user()->secretary->comittee->meetings()->get();

        return view('meeting.list',
            ['instance' => $instance, 'meetings' => $meetings]);
    }

    public function create()
    {
        $instance = \Instantiation::instance();

        $users = User::orderBy('surname')->get();
        $defaultlists = Auth::user()->secretary->default_lists;

        return view('meeting.createandedit',
            ['instance' => $instance, 'users' => $users, 'defaultlists' => $defaultlists, 'route' => route('secretary.meeting.new',$instance)]);
    }

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

    public function edit($instance,$id)
    {
        $meeting = Meeting::find($id);
        $users = User::orderBy('surname')->get();
        $defaultlists = Auth::user()->secretary->default_lists;

        return view('meeting.createandedit',
            ['instance' => $instance, 'meeting' => $meeting, 'edit' => true, 'users' => $users, 'defaultlists' => $defaultlists, 'route' => route('secretary.meeting.save',$instance)]);
    }

    public function defaultlist($instance,$id)
    {
        return DefaultList::find($id)->users;
    }

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

    public function remove(Request $request)
    {
        $meeting = Meeting::find($request->_id);
        $instance = \Instantiation::instance();

        $meeting->delete();

        return redirect()->route('secretary.meeting.list',$instance)->with('success', 'Reunión eliminada con éxito.');
    }
}
