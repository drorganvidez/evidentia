<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Enums\ActivityFlag;
use App\Exports\AttendeesExport;
use App\Facades\Activity;
use App\Http\Controllers\Concerns\PaginatesLists;
use App\Http\Middleware\CheckRoles;
use App\Models\Attendee;
use App\Models\Configuration;
use App\Models\Event;
use App\Models\User;
use App\Services\Activity\SubjectInfo;
use App\Services\SelfReview;
use App\Support\Changes;
use App\Support\ListPage;
use App\Support\ListSort;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EventbriteController extends Controller
{
    use PaginatesLists;

    private const OWN_ATTENDANCE = 'No puedes registrar ni editar tu propia asistencia. El intento queda anotado.';

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
        // Eventbrite responde con un error (que Guzzle lanza) si el token no es válido
        try {
            $client = new Client(['base_uri' => 'https://www.eventbriteapi.com/v3/']);
            $response = $client->request('GET', 'users/me/', [
                'query' => ['token' => $token],
            ]);
        } catch (GuzzleException $e) {
            return false;
        }

        return $response->getStatusCode() == 200;
    }

    public function token_save(Request $request)
    {

        $request->validate([
            'token' => 'required|max:255',
        ]);

        $token = $request->input('token');

        if (! EventbriteController::validate_token($token)) {
            return back()->withInput()->with('error', 'Ups, parece que hay un problema con el token. Comprueba que es válido.');
        }

        DB::transaction(function () use ($token) {
            $configuration = Configuration::find(1);
            $configuration->eventbrite_token = $token;
            $configuration->save();

            // el token nunca se anota
            Activity::record(ActivityAction::EventbriteTokenSaved, $configuration, subjectTitle: 'Token de Eventbrite');
        });

        return redirect()->route('registercoordinator.token')->with('success', 'Token validado y guardado con éxito.');
    }

    public function event_load()
    {
        $token = \Config::eventbrite_token();

        try {
            $client = new Client([
                'base_uri' => 'https://www.eventbriteapi.com/v3/',
            ]);

            // 1. Obtener organizaciones
            $response = $client->request('GET', 'users/me/organizations', [
                'query' => ['token' => $token],
            ]);

            $organizations = json_decode($response->getBody())->organizations ?? [];
            $organizations = (array) $organizations;
            $created = 0;
            $updated = 0;

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
                        $updated++;
                        $saved_event->update($data_event);

                        // Calcular horas
                        $start = new Carbon($saved_event->start_datetime);
                        $end = new Carbon($saved_event->end_datetime);
                        $saved_event->hours = $end->diffInMinutes($start, true) / 60;
                        $saved_event->save();

                    } else {
                        $created++;
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

            // sin transacción: el bucle llama a Eventbrite
            Activity::record(ActivityAction::EventbriteEventsSynced, $config, details: ['created' => $created, 'updated' => $updated]);

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

    // primero por email exacto; si no, por nombre y apellidos exactos, y solo si no hay dudas sobre quién es
    private function find_attendee_user(string $first_name, string $last_name, ?string $email)
    {
        if ($email) {
            $user = DB::table('users')->whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->first();
            if ($user) {
                return $user;
            }
        }

        if ($first_name === '' || $last_name === '') {
            return null;
        }

        $users = DB::table('users')
            ->where('clean_name', $first_name)
            ->where('clean_surname', $last_name)
            ->limit(2)
            ->get();

        return $users->count() === 1 ? $users->first() : null;
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
            $created = 0;
            $updated = 0;
            $unmatched = 0;

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
                    $first_name = \StringUtilites::clean($attendee->profile->first_name);
                    $last_name = \StringUtilites::clean($attendee->profile->last_name);

                    $email = $attendee->profile->email;
                    $status = $attendee->status;

                    // Buscar usuario local
                    $user = $this->find_attendee_user($first_name, $last_name, $email);

                    if (! $user) {
                        $unmatched++;

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
                            $updated++;
                            $record->status = $status;
                            $record->save();
                        }
                    } else {
                        // Crear nuevo registro
                        $created++;
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

            // sin transacción: el bucle llama a Eventbrite; el coordinador de registro puede estar entre los emparejados
            $syncedUserIds = array_values(array_unique(array_map('intval', $syncedUserIds)));
            Activity::record(
                ActivityAction::EventbriteAttendeesSynced,
                $event,
                details: ['created' => $created, 'updated' => $updated, 'unmatched' => $unmatched]
                    + (in_array((int) Auth::id(), $syncedUserIds, true) ? ['self_included' => true] : []),
                targets: $syncedUserIds,
            );

            return redirect()
                ->route('registercoordinator.attendee.list')
                ->with('success', 'Asistencias actualizadas con éxito.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function event_list(Request $request)
    {
        // every event for the register coordinator, hidden ones too, in pages; the newest first by default
        $sort = ListSort::from($request, [
            'name' => 'name',
            'start_datetime' => ListSort::desc('start_datetime'),
            'hours' => 'hours',
            'capacity' => 'capacity',
            'status' => ListSort::rank('status', Event::STATUS_ORDER),
        ], 'start_datetime');
        $events = $this->searchList(Event::query(), $request, ['name']);

        return view('eventbrite.event_list', [
            'events' => $this->paginateList($sort->apply($events), $request), 'term' => ListPage::term($request), 'sort' => $sort,
        ]);
    }

    /**
     * Hide an event so it doesn't appear. Identified by its local id, so it works for hand-made events too.
     */
    public function hide($id)
    {
        $event = Event::find($id);
        if (! $event) {
            return back()->with('error', 'Evento no encontrado.');
        }

        DB::transaction(function () use ($event) {
            $event->hidden = true;
            $event->save();

            Activity::record(ActivityAction::EventHidden, $event);
        });

        return back()->with('success', 'Evento ocultado correctamente.');
    }

    /**
     * Unhide an event so it appears again.
     */
    public function unhide($id)
    {
        $event = Event::find($id);
        if (! $event) {
            return back()->with('error', 'Evento no encontrado.');
        }

        DB::transaction(function () use ($event) {
            $event->hidden = false;
            $event->save();

            Activity::record(ActivityAction::EventUnhidden, $event);
        });

        return back()->with('success', 'Evento visible nuevamente.');
    }

    // Events created by hand, for the sessions held before the edition used Eventbrite

    public function event_create()
    {
        return view('eventbrite.event_create', ['event' => new Event]);
    }

    public function event_store(Request $request)
    {
        $event = new Event;
        $this->fill_manual_event($event, $request, ActivityAction::EventCreated);

        return redirect()->route('registercoordinator.event.list')
            ->with('success', 'Evento creado con éxito.');
    }

    public function event_edit($id)
    {
        return view('eventbrite.event_create', ['event' => $this->manual_event($id)]);
    }

    public function event_update(Request $request, $id)
    {
        $this->fill_manual_event($this->manual_event($id), $request, ActivityAction::EventEdited);

        return redirect()->route('registercoordinator.event.list')
            ->with('success', 'Evento actualizado con éxito.');
    }

    public function event_destroy($id)
    {
        $event = $this->manual_event($id);

        if ($event->attendees()->exists()) {
            return redirect()->route('registercoordinator.event.list')
                ->with('error', 'No se puede borrar un evento con asistencias. Borra antes sus asistencias.');
        }

        // Recorded before the delete, inside the same transaction, while the event still has its name
        DB::transaction(function () use ($event) {
            Activity::record(ActivityAction::EventDeleted, $event, details: ['name' => $event->name]);
            $event->delete();
        });

        return redirect()->route('registercoordinator.event.list')
            ->with('success', 'Evento borrado con éxito.');
    }

    // Only hand-made events are edited here; the Eventbrite ones are overwritten on every load
    private function manual_event($id): Event
    {
        return Event::whereNull('id_eventbrite')->findOrFail($id);
    }

    private function fill_manual_event(Event $event, Request $request, ActivityAction $action): void
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'capacity' => 'nullable|integer|min:0',
        ]);

        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        $before = $event->exists ? $event->only(['name', 'start_datetime', 'end_datetime', 'hours', 'capacity']) : [];

        $event->fill([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'capacity' => $data['capacity'] ?? null,
            // same rule as the Eventbrite events: the length of the session
            'hours' => $end->diffInMinutes($start, true) / 60,
            'status' => $end->isPast() ? 'completed' : 'live',
        ]);

        DB::transaction(function () use ($event, $action, $before) {
            $event->save();
            $after = $event->only(['name', 'start_datetime', 'end_datetime', 'hours', 'capacity']);
            Activity::record($action, $event, details: $before === []
                ? ['name' => $event->name, 'hours' => $event->hours]
                : Changes::between(array_map('strval', $before), array_map('strval', $after)));
        });
    }

    // evento, persona y estado de una asistencia, tal como se anotan
    private function attendee_snapshot(Attendee $attendee): array
    {
        return [
            'event_id' => (int) $attendee->event_id,
            'user_id' => (int) $attendee->user_id,
            'status' => $attendee->status,
        ];
    }

    public function attendee_list(Request $request)
    {
        // the attendances of the course in pages, searched on the server by person or event; the newest event first by default
        $sort = ListSort::from($request, [
            'user' => ['user.surname', 'user.name'],
            'event' => 'event.name',
            'start_datetime' => ListSort::desc('event.start_datetime'),
            'status' => ListSort::rank('status', Attendee::STATUS_ORDER),
        ], 'start_datetime');
        $attendees = $this->searchList(Attendee::query(), $request, ['user.name', 'user.surname', 'user.username', 'event.name'])
            ->with(['user', 'event']);

        return view('eventbrite.attendee_list', [
            'attendees' => $this->paginateList($sort->apply($attendees), $request), 'term' => ListPage::term($request), 'sort' => $sort,
        ]);
    }

    public function attendee_export()
    {
        try {
            // limpiar búfer de salida
            if (ob_get_level()) {
                ob_end_clean();
            }

            Activity::record(ActivityAction::ExportAttendees, details: ['rows' => Attendee::count()]);

            return Excel::download(new AttendeesExport, 'asistencias'.\Illuminate\Support\Carbon::now().'.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: '.$e->getMessage());
        }
    }

    public function attendee_edit($id)
    {
        $attendee = Attendee::findOrFail($id);
        $events = Event::orderByDesc('start_datetime')->get();
        $users = User::orderBy('surname')->get();

        return view('eventbrite.attendee_edit', compact('attendee', 'events', 'users'));
    }

    public function attendee_update(Request $request, $id)
    {
        $attendee = Attendee::findOrFail($id);
        $before = $this->attendee_snapshot($attendee);

        $request->validate([
            'event_id' => 'required|exists:events,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string|max:255',
        ]);

        // nadie registra ni edita su propia asistencia, ni pidiéndola ni sobre la que ya tiene
        if (SelfReview::violates(Auth::user(), (int) $request->user_id) || SelfReview::violates(Auth::user(), $attendee)) {
            SelfReview::deny($attendee, ActivityAction::AttendeeEdited, Auth::user(), self::OWN_ATTENDANCE);
        }

        // Comprobar si ya existe otra asistencia con esa tupla usuario-evento
        $exists = Attendee::where('event_id', $request->event_id)
            ->where('user_id', $request->user_id)
            ->where('id', '!=', $attendee->id)
            ->exists();

        if ($exists) {
            return redirect()
                ->route('registercoordinator.attendee.list')
                ->with('error', 'Ya existe una asistencia para ese usuario y ese evento.');
        }

        DB::transaction(function () use ($request, $attendee, $before) {
            $attendee->update($request->only('event_id', 'user_id', 'status'));

            Activity::record(
                ActivityAction::AttendeeEdited,
                $attendee,
                $attendee->user()->first(),
                details: Changes::between($before, $this->attendee_snapshot($attendee)),
            );
        });

        return redirect()->route('registercoordinator.attendee.list')
            ->with('success', 'Asistencia actualizada con éxito.');
    }

    public function attendee_create()
    {
        $events = Event::orderByDesc('start_datetime')->get();
        $users = User::orderBy('surname')->get();

        return view('eventbrite.attendee_create', compact('events', 'users'));
    }

    public function attendee_store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string|max:255',
        ]);

        // nadie registra su propia asistencia
        if (SelfReview::violates(Auth::user(), (int) $request->user_id)) {
            SelfReview::deny(new Attendee($request->only(['event_id', 'user_id', 'status'])), ActivityAction::AttendeeCreated, Auth::user(), self::OWN_ATTENDANCE);
        }

        // Comprobar si ya existe una asistencia con esa tupla usuario-evento
        $exists = Attendee::where('event_id', $request->event_id)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($exists) {
            return redirect()
                ->route('registercoordinator.attendee.list')
                ->with('error', 'Ya existe una asistencia para ese usuario y ese evento.');
        }

        DB::transaction(function () use ($request) {
            $attendee = Attendee::create($request->only('event_id', 'user_id', 'status'));

            Activity::record(ActivityAction::AttendeeCreated, $attendee, $attendee->user, details: ['status' => $attendee->status]);
        });

        return redirect()->route('registercoordinator.attendee.list')
            ->with('success', 'Asistencia creada con éxito.');
    }

    public function attendee_destroy($id)
    {
        $attendee = Attendee::findOrFail($id);
        $title = SubjectInfo::title($attendee);
        $person = $attendee->user;

        DB::transaction(function () use ($attendee, $title, $person) {
            $attendee->delete();

            // borrar la asistencia propia no se bloquea, pero queda señalado
            Activity::record(
                ActivityAction::AttendeeDeleted,
                $attendee,
                $person,
                details: ['status' => $attendee->status],
                flags: (int) $attendee->user_id === (int) Auth::id() ? [ActivityFlag::RoleOverlap] : [],
                subjectTitle: $title,
            );
        });

        return redirect()
            ->route('registercoordinator.attendee.list')
            ->with('success', 'Asistencia eliminada con éxito.');
    }
}
