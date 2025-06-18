<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendeesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $users = User::all();
        $users = $users->sortBy('clean_surname');
        $res = collect();
        foreach($users as $user){

            // los profesores no se incluyen
            if(!$user->hasRole('LECTURE')) {

                $array = [
                    'apellidos' => strtoupper(trim($user->surname)),
                    'nombre' => strtoupper(trim($user->name)),
                    'uvus' => trim($user->username),
                    'horas_asistencia' => $user->attendees_hours(),
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
            'Apellidos',
            'Nombre',
            'Uvus',
            'Horas de asistencia'
        ];
    }
}
