<?php

namespace App\Exports;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MyAttendeesExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $asistencias = Auth::user()->attendees;

        $res = collect();
        foreach ($asistencias as $asistencia) {

            if (Auth::User()->hasRole('STUDENT')) {

                $array = [
                    'Evento' => strtoupper(trim($asistencia->event->name)),
                    'Horas' => strtoupper(trim($asistencia->event->hours)),
                    'Fecha inicio' => strtoupper(trim($asistencia->event->start_datetime)),
                    'Fecha fin' => strtoupper(trim($asistencia->event->end_datetime)),
                    'Estado' => strtoupper(trim($asistencia->status)),

                ];

                $object = (object) $array;
                $res->push($object);
            }
        }

        return $res;
    }

    public function headings(): array
    {
        return [
            'Evento',
            'Horas',
            'Fecha inicio',
            'Fecha fin',
            'Estado',
        ];
    }
}
