<?php

namespace App\Http\Controllers;

use App\Exports\AttendeesExport;
use App\Exports\EventsExport;
use App\Http\Middleware\CheckRoles;
use App\Models\Attendee;
use App\Models\Configuration;
use App\Models\Event;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EventbriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':REGISTER_COORDINATOR');
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
            'query' => ['token' => $token],
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
            $client = new \GuzzleHttp\Client([
                'base_uri' => 'https://www.eventbriteapi.com/v3/',
            ]);

            // 1. Obtener organizaciones
            $response = $client->request('GET', 'users/me/organizations', [
                'query' => ['token' => $token],
            ]);

            $organizations = json_decode($response->getBody())->organizations ?? [];
            $organizations = (array) $organizations;

            foreach ($organizations as $organization) {

                $organization_id = $organization->id;

                // ==============================================
                // 2. PAGINACIÓN DE EVENTOS
                // ==============================================
                $all_events = [];
                $page = 1;

                do {
                    $resp_page = $client->request('GET', 'organizations/'.$organization_id.'/events', [
                        'query' => [
                            'token' => $token,
                            'page' => $page,
                            'page_size' => 200, // máximo permitido
                        ],
                    ]);

                    $data = json_decode($resp_page->getBody());

                    $events_page = $data->events ?? [];
                    $pagination = $data->pagination ?? null;

                    if (! empty($events_page)) {
                        $all_events = array_merge($all_events, $events_page);
                    }

                    $page++;

                } while ($pagination && $pagination->has_more_items);

                // ==============================================
                // 3. GUARDADO / ACTUALIZACIÓN DE EVENTOS
                // ==============================================
                foreach ($all_events as $event) {

                    $saved_event = Event::where('id_eventbrite', $event->id)->first();

                    $data_event = [
                        'name' => $event->name->html,
                        'description' => $event->description->html,
                        'start_datetime' => $event->start->local,
                        'end_datetime' => $event->end->local,
                        'capacity' => $event->capacity,
                        'status' => $event->status,
                        'url' => $event->url,
                    ];

                    if ($saved_event) {
                        $saved_event->update($data_event);

                        // Calcular horas
                        $start = new Carbon($saved_event->start_datetime);
                        $end = new Carbon($saved_event->end_datetime);
                        $saved_event->hours = $end->diffInMinutes($start, true) / 60;
                        $saved_event->save();

                    } else {
                        $new_event = Event::create(array_merge($data_event, [
                            'id_eventbrite' => $event->id,
                        ]));

                        // Calcular horas
                        $start = new Carbon($new_event->start_datetime);
                        $end = new Carbon($new_event->end_datetime);
                        $new_event->hours = $end->diffInMinutes($start, true) / 60;
                        $new_event->save();
                    }
                }
            }

            // 4. Marca de tiempo
            $config = Configuration::find(1);
            $config->events_uploaded_timestamp = Carbon::now();
            $config->save();

            return redirect()
                ->route('registercoordinator.event.list')
                ->with('success', 'Eventos cargados con éxito.');

        } catch (\Exception $e) {
            return back()->with(
                'error',
                'Ups, parece que hay un problema con el token o la API. '.$e->getMessage()
            );
        }
    }

    public function attendee_load($eventbrite_id)
    {
        $token = \Config::eventbrite_token();

        // Obtener el evento local usando el id_eventbrite
        $event = Event::where('id_eventbrite', $eventbrite_id)->firstOrFail();

        if (! EventbriteController::validate_token($token)) {
            return back()->with('error', 'Ups, parece que hay un problema con el token.');
        }

        try {
            $client = new Client(['base_uri' => 'https://www.eventbriteapi.com/v3/']);
            $page = 1;

            // Para evitar duplicados de user_id
            $syncedUserIds = [];

            do {
                // Llamada a Eventbrite
                $response = $client->request('GET', "events/{$event->id_eventbrite}/attendees", [
                    'query' => ['token' => $token, 'page' => $page],
                ]);

                $data = json_decode($response->getBody());
                $attendees = $data->attendees;
                $has_more_items = $data->pagination->has_more_items;

                foreach ($attendees as $attendee) {

                    // Limpieza nombres
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

                    // Buscar usuario local
                    $user = DB::table('users')
                        ->where('clean_name', 'like', "%$first_name%")
                        ->where('clean_surname', 'like', "%$last_name%")
                        ->first();

                    if (! $user) {
                        $user = DB::table('users')->where('email', 'like', "%$email%")->first();
                    }

                    if (! $user) {
                        continue; // Ignorar usuarios sin correspondencia en tu sistema
                    }

                    $syncedUserIds[] = $user->id;

                    // Buscar asistencia usando el id interno del evento
                    $record = Attendee::where('event_id', $event->id)
                        ->where('user_id', $user->id)
                        ->first();

                    if ($record) {
                        // Actualizar solo si cambia algo
                        if ($record->status !== $status) {
                            $record->status = $status;
                            $record->save();
                        }
                    } else {
                        // Crear nuevo registro
                        Attendee::create([
                            'event_id' => $event->id,   // <-- AHORA CORRECTO
                            'user_id' => $user->id,
                            'status' => $status,
                        ]);
                    }
                }

                $page++;

            } while ($has_more_items);

            // Actualiza timestamp de sincronización
            $config = Configuration::find(1);
            $config->attendees_uploaded_timestamp = Carbon::now();
            $config->save();

            return redirect()
                ->route('registercoordinator.attendee.list')
                ->with('success', 'Asistencias actualizadas con éxito.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function event_list()
    {

        // Show all events to the register coordinator (including hidden ones)
        $events = Event::all();

        return view('eventbrite.event_list', ['events' => $events]);
    }

    /**
     * Hide an event so it doesn't appear.
     */
    public function hide($id_eventbrite)
    {
        $event = Event::where('id_eventbrite', $id_eventbrite)->first();
        if (! $event) {
            return back()->with('error', 'Evento no encontrado.');
        }

        $event->hidden = true;
        $event->save();

        return back()->with('success', 'Evento ocultado correctamente.');
    }

    /**
     * Unhide an event so it appears again.
     */
    public function unhide($id_eventbrite)
    {
        $event = Event::where('id_eventbrite', $id_eventbrite)->first();
        if (! $event) {
            return back()->with('error', 'Evento no encontrado.');
        }

        $event->hidden = false;
        $event->save();

        return back()->with('success', 'Evento visible nuevamente.');
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

            return Excel::download(new AttendeesExport, 'asistencias'.\Illuminate\Support\Carbon::now().'.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: '.$e->getMessage());
        }
    }

    public function events_export($ext)
    {
        try {
            if (ob_get_level()) {
                ob_end_clean();
            }
            if (! in_array($ext, ['csv', 'pdf', 'xlsx'])) {
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }

            return Excel::download(new EventsExport, 'eventos-'.\Illuminate\Support\Carbon::now().'.'.$ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: '.$e->getMessage());
        }
    }
}
