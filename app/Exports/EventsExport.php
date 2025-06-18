<?php

namespace App\Exports;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EventsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $events = Event::all();
        $res = collect();
        foreach ($events as $event) {

            if (Auth::User()->hasRole('REGISTER_COORDINATOR')) {

                $array = [
                    'Nombre' => strtoupper(trim($event->name)),
                    'Fecha_inicio' => strtoupper(trim($event->start_datetime)),
                    'Fecha_fin' => strtoupper(trim($event->end_datetime)),
                    'Capacidad' => strtoupper(trim($event->capacity)),
                    'Horas' => strtoupper(trim($event->hours)),
                    'Estado' => strtoupper(trim($event->status)),
                    'Url' => trim($event->url),
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
            'Nombre',
            'Fecha inicio',
            'Fecha fin',
            'Capacidad',
            'Horas',
            'Estado',
            'Url',
        ];
    }
}
