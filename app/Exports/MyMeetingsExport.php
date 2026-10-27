<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MyMeetingsExport implements FromCollection, WithHeadings
{
    /**
     * @return Collection
     */
    public function collection()
    {
        $meetings = Auth::user()->meetings()->get();

        $res = collect();
        foreach ($meetings as $meeting) {

            if (Auth::User()->hasRole('STUDENT')) {

                $array = [
                    'Reunión' => strtoupper(trim($meeting->title)),
                    'Lugar' => strtoupper(trim($meeting->place)),
                    'Horas' => strtoupper(trim($meeting->hours)),
                    'Fecha de realización' => strtoupper(trim($meeting->datetime)),
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
            'Reunión',
            'Lugar',
            'Horas',
            'Fecha de realización',
        ];
    }
}
