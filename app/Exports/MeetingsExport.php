<?php

namespace App\Exports;

use App\Models\Meeting;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;

class MeetingsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function user_list()
    {
        $users = User::all();

        // el presidente no puede editar los usuarios de tipo profesor
        $filtered_users = collect();
        $users->each(function ($item, $key) use ($filtered_users) {
            if (!$item->hasRole('LECTURE')) {
                $filtered_users->push($item);
            }
        });

        return $filtered_users;
    }

    public function collection()
    {
        $meetings= Meeting::all();
        $res = collect();
        foreach($meetings as $meeting){

            if(Auth::User()->hasRole('PRESIDENT') or Auth::User()->hasRole('LECTURE')) {

                $array = [
                    'Reunion' => strtoupper(trim($meeting->title)),
                    'Lugar' => strtoupper(trim($meeting->place)),
                    'Horas' => strtoupper(trim($meeting->hours)),
                    'Comite' => strtoupper(trim($meeting->comittee->name)),
                    'NºAsistentes' => strtoupper(trim($meeting->users->count())),
                    'Realizada' => strtoupper(trim($meeting->datetime))

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
            'Comité',
            'NºAsistentes',
            'Realizada'

        ];
    }

}
