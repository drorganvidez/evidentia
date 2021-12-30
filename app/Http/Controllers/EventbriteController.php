<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Configuration;
use App\Models\Event;
use App\Exports\AttendeesExport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EventbriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:REGISTER_COORDINATOR');
    }

    public function token()
    {
        $instance = \Instantiation::instance();
        $token = \Config::eventbrite_token();
        $route = route('registercoordinator.token.save',$instance);

        return view('eventbrite.token',['instance' => $instance, 'token' => $token, 'route' => $route]);
    }

    private static function validate_token($token)
    {
        $client = new Client(['base_uri' => 'https://www.eventbriteapi.com/v3/']);
        $response = $client->request('GET', 'users/me/', [
            'query' => ['token' => $token]
        ]);

        return $response->getStatusCode() == 200 ? true : false;

    }

    public function token_save(Request $request)
    {
        $instance = \Instantiation::instance();

        $request->validate([
            'token' => 'required|max:255',
        ]);

        $token = $request->input('token');

        try {
            if(EventbriteController::validate_token($token)){
                $configuration = Configuration::find(1);
                $configuration->eventbrite_token = $token;
                $configuration->save();
                return redirect()->route('registercoordinator.token', $instance)->with('success', 'Token validado y guardado con éxito.');
            }
        }catch(\Exception $e){
            return back()->withInput()->with('error', 'Ups, parece que hay un problema con el token. Comprueba que es válido.');
        }


    }

    public function event_load()
    {

        $instance = \Instantiation::instance();

        $token = \Config::eventbrite_token();

        try {
            $client = new Client(['base_uri' => 'https://www.eventbriteapi.com/v3/']);

            // obtenemos las organizaciones
            $organizations = $client->request('GET', 'users/me/organizations', [
                'query' => ['token' => $token]
            ]);
            $organizations = json_decode($organizations->getBody());
            $organizations = (array)$organizations->organizations;

            // se itera sobre todas las organizaciones, si hubiera más de una
            foreach ($organizations as $organization) {

                // averiguamos el ID de la organizacion
                $organization_id = $organization->id;

                // obtenemos los eventos
                $events = $client->request('GET', 'organizations/' . $organization_id . '/events', [
                    'query' => ['token' => $token]
                ]);
                $events = json_decode($events->getBody());
                $events = (array)$events->events;

                foreach ($events as $event) {

                    // ¿el evento ha sido ya creado?
                    $saved_event = Event::where('id_eventbrite', $event->id)->first();
                    if ($saved_event != null) {
                        $saved_event->name = $event->name->html;
                        $saved_event->description = $event->description->html;
                        $saved_event->start_datetime = $event->start->local;
                        $saved_event->end_datetime = $event->end->local;
                        $saved_event->capacity = $event->capacity;
                        $saved_event->status = $event->status;
                        $saved_event->url = $event->url;

                        // Cálculo de las horas
                        $start = new Carbon($saved_event->start_datetime);
                        $end = new Carbon($saved_event->end_datetime);
                        $hours = $end->diffInMinutes($start,true);
                        $hours = $hours/60;
                        $saved_event->hours = $hours;

                        $saved_event->save();
                    } else {
                        $new_event = Event::create([
                            'name' => $event->name->html,
                            'description' => $event->description->html,
                            'id_eventbrite' => $event->id,
                            'start_datetime' => $event->start->local,
                            'end_datetime' => $event->end->local,
                            'capacity' => $event->capacity,
                            'status' => $event->status,
                            'url' => $event->url
                        ]);

                        // Cálculo de las horas
                        $start = new Carbon($new_event->start_datetime);
                        $end = new Carbon($new_event->end_datetime);
                        $hours = $end->diffInMinutes($start,true);
                        $hours = $hours/60;
                        $new_event->hours = $hours;

                        $new_event->save();
                    }

                }

            }

            $config = Configuration::find(1);
            $config->events_uploaded_timestamp = Carbon::now();
            $config->save();

            return redirect()->route('registercoordinator.event.list', $instance)->with('success', 'Eventos cargados con éxito.');

        }catch (\Exception $e){
            return back()->with('error', 'Ups, parece que hay un problema con el token. Comprueba que es válido.' . $e->getMessage());
        }
    }

    public function attendee_load()
    {

        $instance = \Instantiation::instance();

        $token = \Config::eventbrite_token();
        $events = Event::all();

        // PRUEBA DE CONEXIÓN CON TOKEN
        if(!EventbriteController::validate_token($token)){
            return back()->with('error', 'Ups, parece que hay un problema con el token. Comprueba que es válido...');
        }

        // antes de continuar, lo más fácil y rápido es borrar las asistencias previas
        DB::table('attendee')->delete();

        try {
            $client = new Client(['base_uri' => 'https://www.eventbriteapi.com/v3/']);

            foreach($events as $event) {

                // obtenemos la paginación
                $attendees = $client->request('GET', 'events/' . $event->id_eventbrite .'/attendees', [
                    'query' => ['token' => $token]
                ]);

                $attendees = json_decode($attendees->getBody());
                $page_count = $attendees->pagination->page_count;

                $page_contador = 1;

                while($page_contador <= $page_count){

                    // obtenemos las asistencias a cada evento
                    $attendees_page = $client->request('GET', 'events/' . $event->id_eventbrite .'/attendees', [
                        'query' => ['token' => $token, 'page' => $page_contador]
                    ]);

                    $attendees_page = json_decode($attendees_page->getBody());
                    $attendees_page = (array)$attendees_page->attendees;

                    foreach($attendees_page as $attendee){

                        // limpiamos el nombre de caracteres problemáticos
                        $first_name = $attendee->profile->first_name;
                        $first_name = strtoupper(trim(preg_replace('~[^0-9a-z]+~i', '', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($first_name, ENT_QUOTES, 'UTF-8'))), ' '));

                        // limpiamos los apellidos de caracteres problemáticos
                        $last_name = $attendee->profile->last_name;
                        $last_name = strtoupper(trim(preg_replace('~[^0-9a-z]+~i', '', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($last_name, ENT_QUOTES, 'UTF-8'))), ' '));

                        $status = $attendee->status;
                        $email = $attendee->profile->email;

                        $user = DB::table('users')->where('clean_name', 'like', '%' . $first_name . '%')->where('clean_surname', 'like', '%' . $last_name . '%')->first();

                        // si no hemos encontrado al usuario por nombres y apellidos, lo intentamos por el email
                        if($user == null){
                            $user = DB::table('users')->where('email', 'like', '%' . $email . '%')->first();
                        }

                        // usuario encontrado
                        if($user != null){

                            $exits = Attendee::where("event_id", $event->id)->where("user_id", $user->id)->first();

                            if($exits == null){
                                try{

                                    Attendee::create([
                                        'event_id' => $event->id,
                                        'user_id' => $user->id,
                                        'status' => $status
                                    ]);

                                }catch (\Exception $e){
                                    echo $e;
                                }
                            }


                        }
                    }

                    $page_contador = $page_contador +1;

                }

            }

            $config = Configuration::find(1);
            $config->attendees_uploaded_timestamp = Carbon::now();
            $config->save();

            return redirect()->route('registercoordinator.attendee.list', $instance)->with('success', 'Asistencias actualizadas con éxito.');

        }catch (\Exception $e){
            return back()->with('error', $e);
        }
    }

    public function event_list()
    {
        $instance = \Instantiation::instance();

        $events = Event::all();

        return view('eventbrite.event_list',
            ['instance' => $instance, 'events' => $events]);
    }

    public function attendee_list()
    {
        $instance = \Instantiation::instance();

        $attendees = Attendee::all();

        return view('eventbrite.attendee_list',
            ['instance' => $instance, 'attendees' => $attendees]);
    }

    public function attendee_export()
    {
        try{
            // limpiar búfer de salida
            ob_end_clean();
            return Excel::download(new AttendeesExport(), 'asistencias' . \Illuminate\Support\Carbon::now() . '.xlsx');
        }catch(\Exception $e){
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }
}
