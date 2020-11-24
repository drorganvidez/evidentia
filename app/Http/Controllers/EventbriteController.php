<?php

namespace App\Http\Controllers;

use App\Attendee;
use App\Configuration;
use App\Event;
use App\User;
use Carbon\Carbon;
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

    function remove_accents($string) {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
            chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
        );

        $string = strtr($string, $chars);

        return $string;
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

                // obtenemos las asistencias a cada evento
                $attendees = $client->request('GET', 'events/' . $event->id_eventbrite .'/attendees', [
                    'query' => ['token' => $token]
                ]);

                $attendees = json_decode($attendees->getBody());
                $attendees = (array)$attendees->attendees;
                foreach($attendees as $attendee){


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

                        $new_attendee = Attendee::create([
                            'event_id' => $event->id,
                            'user_id' => $user->id,
                            'status' => $status
                        ]);
                        $new_attendee->save();
                    }
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
}
