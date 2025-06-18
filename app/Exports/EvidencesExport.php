<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EvidencesExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public $evidences_select = null;

    public $meetings_select = null;

    public $events_select = null;

    public $bonus = null;

    public function __construct($evidences_select, $meetings_select, $events_select, $bonus)
    {
        $this->evidences_select = $evidences_select;
        $this->meetings_select = $meetings_select;
        $this->events_select = $events_select;
        $this->bonus = $bonus;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {

        $users = User::all();
        $users = $users->sortBy('clean_surname');
        $res = collect();
        foreach ($users as $user) {

            // los profesores no se incluyen
            if (! $user->hasRole('LECTURE')) {

                $array = [
                    'apellidos' => $user->surname,
                    'nombre' => $user->name,
                    'uvus' => $user->username,
                    'correo' => $user->email,
                    'perfil' => route('profiles.view', ['id' => $user->id]),
                    'participacion' => $user->participation,
                    'comite' => $user->committee_belonging(),
                    'evidencia_aleatoria' => $user->evidence_rand_route(),
                    'horas_evidencia_aleatoria' => $user->evidence_rand_hours(),
                    'eventos_asistidos' => $user->events_count(),
                    'horas_asistencia' => $user->max_events_hours(),
                    'reuniones_asistidas' => $user->meetings_count(),
                    'horas_reuniones' => $user->meetings_hours(),
                    'bono_horas' => $user->bonus_hours(),
                    'evidencias_aceptadas' => $user->evidences_accepted_count(),
                    'horas_evidencias' => $user->evidences_accepted_hours(),
                    'horas_totales' => 0,
                ];

                // se descartan las columnas no seleccionadas
                if ($this->events_select != 'on') {
                    unset($array['eventos_asistidos']);
                    unset($array['horas_asistencia']);
                }

                if ($this->meetings_select != 'on') {
                    unset($array['reuniones_asistidas']);
                    unset($array['horas_reuniones']);
                }

                if ($this->evidences_select != 'on') {
                    unset($array['evidencias_aceptadas']);
                    unset($array['horas_evidencias']);
                }

                if ($this->bonus != 'on') {
                    unset($array['bono_horas']);
                }

                // horas en total
                $horas_totales = 0;
                if ($this->events_select == 'on') {
                    $horas_totales += $user->max_events_hours();
                }
                if ($this->meetings_select == 'on') {
                    $horas_totales += $user->meetings_hours();
                }
                if ($this->evidences_select == 'on') {
                    $horas_totales += $user->evidences_accepted_hours();
                }
                if ($this->bonus == 'on') {
                    $horas_totales += $user->bonus_hours();
                }

                $array['horas_totales'] = $horas_totales;

                $object = (object) $array;
                $res->push($object);

            }

        }

        return $res;
    }

    public function headings(): array
    {
        $events_select = $this->events_select;
        $cabeceras = [
            'Apellidos',
            'Nombre',
            'Uvus',
            'Correo',
            'Perfil',
            'Participación',
            'Comité',
            'Evidencia aleatoria',
            'Horas de evidencia aleatoria',
            'Eventos asistidos',
            'Horas de asistencia',
            'Reuniones asistidas',
            'Horas de reuniones',
            'Bono de horas',
            'Evidencias registradas',
            'Horas de evidencias',
            'Horas en total',
        ];

        // se descartan las columnas no seleccionadas
        if ($this->events_select != 'on') {
            if (($key = array_search('Eventos asistidos', $cabeceras)) !== false) {
                unset($cabeceras[$key]);
            }
            if (($key = array_search('Horas de asistencia', $cabeceras)) !== false) {
                unset($cabeceras[$key]);
            }
        }

        if ($this->meetings_select != 'on') {
            if (($key = array_search('Reuniones asistidas', $cabeceras)) !== false) {
                unset($cabeceras[$key]);
            }
            if (($key = array_search('Horas de reuniones', $cabeceras)) !== false) {
                unset($cabeceras[$key]);
            }
        }

        if ($this->evidences_select != 'on') {
            if (($key = array_search('Evidencias registradas', $cabeceras)) !== false) {
                unset($cabeceras[$key]);
            }
            if (($key = array_search('Horas de evidencias', $cabeceras)) !== false) {
                unset($cabeceras[$key]);
            }
        }

        if ($this->bonus != 'on') {
            if (($key = array_search('Bono de horas', $cabeceras)) !== false) {
                unset($cabeceras[$key]);
            }
        }

        return $cabeceras;
    }
}
