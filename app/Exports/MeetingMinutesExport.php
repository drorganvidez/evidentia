<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MeetingMinutesExport implements FromCollection, WithHeadings
{
    /**
     * @return Collection
     */
    public function collection()
    {
        $meeting_minutes = Auth::user()->secretary->meetingMinutes;
        $res = collect();
        foreach ($meeting_minutes as $m) {

            if (Auth::User()->hasRole('SECRETARY')) {

                $array = [
                    'Titulo' => strtoupper(trim($m->meeting->title)),
                    'Lugar' => strtoupper(trim($m->meeting->place)),
                    'Realizada' => strtoupper(Carbon::parse($m->meeting->datetime)->format('d/m/Y').' '.
                                                    Carbon::parse($m->meeting->datetime)->format('H:i')),
                    'Duración' => strtoupper(trim($m->meeting->hours)),
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
            'Título',
            'Lugar',
            'Fecha de realización',
            'Horas',
        ];
    }
}
