<?php

namespace App\Http\Controllers;

use App\Attendee;
use App\Configuration;
use App\Event;
use App\User;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                        $new_event->save();
                    }

                }
            }

            return redirect()->route('registercoordinator.event.list', $instance)->with('success', 'Eventos cargados con éxito.');

        }catch (\Exception $e){
            return back()->with('error', 'Ups, parece que hay un problema con el token. Comprueba que es válido.');
        }
    }

    public function attendee_load()
    {
        $instance = \Instantiation::instance();

        $token = \Config::eventbrite_token();
        $events = Event::all();

        // PRUEBA DE CONEXIÓN CON TOKEN
        if(!EventbriteController::validate_token($token)){
            return back()->with('error', 'Ups, parece que hay un problema con el token. Comprueba que es válido.');
        }

        // antes de continuar, lo más fácil y rápido es borrar las asistencias previas
        DB::table('attendee')->delete();

        try {
            $client = new Client(['base_uri' => 'https://www.eventbriteapi.com/v3/']);

            foreach($events as $event) {

                // obtenemos las asistencias a cada evento
                $attendees = $client->request('GET', 'events/' . $event->id_eventbrite .'/attendees', [
                    'query' => ['token' => $token]
                ]);

                $attendees = json_decode($attendees->getBody());
                $attendees = (array)$attendees->attendees;
                foreach($attendees as $attendee){
                    $first_name = $attendee->profile->first_name;
                    $last_name = $attendee->profile->last_name;
                    $status = $attendee->status;
                    $user = User::where([
                        ['name', '=', $first_name],
                        ['surname', '=', $last_name]
                    ])->first();

                    // usuario encontrado
                    if($user != null){

                        $new_attendee = Attendee::create([
                            'event_id' => $event->id,
                            'user_id' => $user->id,
                            'status' => $status
                        ]);
                        $new_attendee->save();
                    }
                }

            }

            return redirect()->route('registercoordinator.attendee.list', $instance)->with('success', 'Asistencias cargadas con éxito.');

        }catch (\Exception $e){
            return back()->with('error', 'Ups, parece que hay un problema con el token. Comprueba que es válido.');
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
}
