<?php

namespace App\Http\Controllers;

use App\Exports\EventsExport;
use App\Exports\AttendeesExport;
use App\Models\Attendee;
use App\Models\Configuration;
use App\Models\Event;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Middleware\CheckRoles;

class EventbriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class . ':REGISTER_COORDINATOR');
    }

    public function token()
    {
        
        $token = \Config::eventbrite_token();
        $route = route('registercoordinator.token.save');

        return view('eventbrite.token', ['token' => $token, 'route' => $route]);
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
        

        $request->validate([
            'token' => 'required|max:255',
        ]);

        $token = $request->input('token');

        try {
            if (EventbriteController::validate_token($token)) {
                $configuration = Configuration::find(1);
                $configuration->eventbrite_token = $token;
                $configuration->save();
                return redirect()->route('registercoordinator.token')->with('success', 'Token validado y guardado con éxito.');
            }
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Ups, parece que hay un problema con el token. Comprueba que es válido.');
        }


    }

    public function event_load()
    {

        

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
                        $hours = $end->diffInMinutes($start, true);
                        $hours = $hours / 60;
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
                        $hours = $end->diffInMinutes($start, true);
                        $hours = $hours / 60;
                        $new_event->hours = $hours;

                        $new_event->save();
                    }

                }

            }

            $config = Configuration::find(1);
            $config->events_uploaded_timestamp = Carbon::now();
            $config->save();

            return redirect()->route('registercoordinator.event.list')->with('success', 'Eventos cargados con éxito.');

        } catch (\Exception $e) {
            return back()->with('error', 'Ups, parece que hay un problema con el token. Comprueba que es válido.' . $e->getMessage());
        }
    }

    public function attendee_load($event_id)
    {
        $token = \Config::eventbrite_token();
        $event = Event::where('id_eventbrite', $event_id)->first();
        
        // Validar el token
        if (!EventbriteController::validate_token($token)) {
            return back()->with('error', 'Ups, parece que hay un problema con el token. Comprueba que es válido...');
        }

        // Borrar asistencias previas relacionadas con el evento
        DB::table('attendee')->where('event_id', $event_id)->delete();

        try {
            $client = new Client(['base_uri' => 'https://www.eventbriteapi.com/v3/']);
            $page = 1;

            do {
                // Obtener los asistentes de la página actual
                $response = $client->request('GET', 'events/' . $event->id_eventbrite . '/attendees', [
                    'query' => ['token' => $token, 'page' => $page]
                ]);

                $data = json_decode($response->getBody());
                $attendees = $data->attendees;
                $has_more_items = $data->pagination->has_more_items;

                foreach ($attendees as $attendee) {
                    $first_name = strtoupper(trim(preg_replace('~[^0-9a-z]+~i', '', preg_replace(
                        '~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i',
                        '$1',
                        htmlentities($attendee->profile->first_name, ENT_QUOTES, 'UTF-8')
                    )), ' '));

                    $last_name = strtoupper(trim(preg_replace('~[^0-9a-z]+~i', '', preg_replace(
                        '~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i',
                        '$1',
                        htmlentities($attendee->profile->last_name, ENT_QUOTES, 'UTF-8')
                    )), ' '));

                    $email = $attendee->profile->email;
                    $status = $attendee->status;

                    $user = DB::table('users')->where('clean_name', 'like', '%' . $first_name . '%')->where('clean_surname', 'like', '%' . $last_name . '%')->first();

                    // si no hemos encontrado al usuario por nombres y apellidos, lo intentamos por el email
                    if ($user == null) {
                        $user = DB::table('users')->where('email', 'like', '%' . $email . '%')->first();
                    }

                    // usuario encontrado
                    if ($user != null) {
                        $exits = Attendee::where("event_id", $event_id)->where("user_id", $user->id)->first();
                        if($exits == null){
                            try{
                                $new_attendee = Attendee::create([
                                    'event_id' => $event->id,
                                    'user_id' => $user->id,
                                    'status' => $status
                                ]);
                                $new_attendee->save();

                            }catch (\Exception $e){
                                return back()->with('error', $e->getMessage());
                            }
                        }
                    }
                }

                $page++;

            } while ($has_more_items);

            $config = Configuration::find(1);
            $config->attendees_uploaded_timestamp = Carbon::now();
            $config->save();

            return redirect()->route('registercoordinator.attendee.list')
                ->with('success', 'Asistencias actualizadas con éxito.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function event_list()
    {
        

        $events = Event::all();

        return view('eventbrite.event_list',
            ['events' => $events]);
    }

    public function attendee_list()
    {
        

        $attendees = Attendee::all();

        return view('eventbrite.attendee_list',
            ['attendees' => $attendees]);
    }

    public function attendee_export()
    {
        try {
            // limpiar búfer de salida
            if (ob_get_level()) {
                ob_end_clean();
            }
            return Excel::download(new AttendeesExport(), 'asistencias' . \Illuminate\Support\Carbon::now() . '.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function events_export($ext)
    {
        try {
            if (ob_get_level()) {
                ob_end_clean();
            }
            if (!in_array($ext, ['csv', 'pdf', 'xlsx'])) {
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new EventsExport(), 'eventos-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }
}
