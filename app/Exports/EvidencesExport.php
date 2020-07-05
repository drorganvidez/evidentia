<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EvidencesExport implements FromCollection, WithHeadings, ShouldAutoSize
{

    private $evidences_select = null;
    private $meetings_select = null;
    private $events_select = null;

    public function __construct($evidences_select,$meetings_select,$events_select){
        $this->evidences_select = $evidences_select;
        $this->meetings_select = $meetings_select;
        $this->events_select = $events_select;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $users = User::all();
        $res = collect();
        foreach($users as $user){

            // los profesores no se incluyen
            if(!$user->hasRole('LECTURE')) {

                $res->push(
                    (object)[
                        'dni' => $user->dni,
                        'apellidos' => $user->surname,
                        'nombre' => $user->name,
                        'uvus' => $user->username,
                        'correo' => $user->email,
                        'participacion' => $user->participation,
                        'eventos_asistidos' => $user->events_count(),
                        'horas_asistencia' => $user->events_hours(),
                        'reuniones_asistidas' => $user->meetings_count(),
                        'horas_reuniones' => $user->meetings_hours(),
                        'bono_horas' => $user->bonus_hours(),
                        'evidencias_aceptadas' => $user->evidences_accepted_count(),
                        'horas_evidencias' => $user->evidences_accepted_hours(),
                        'horas_totales_' => 0
                    ]);

            }

        }

        $res = $res->sortBy('surname')->sortBy('participation')->sortBy('surname');
        return $res;
    }

    public function headings(): array
    {
        return [
            'D.N.I.',
            'Apellidos',
            'Nombre',
            'Uvus',
            'Correo',
            'Participación',
            'Eventos asistidos',
            'Horas de asistencia',
            'Reuniones asistidas',
            'Horas de reuniones',
            'Bono de horas',
            'Evidencias registradas',
            'Horas de evidencias',
            'Horas en total'
        ];
    }
}
