<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

// estricto con los nulos: unas horas a 0 se exportan como 0, no como una celda vacía
class AttendeesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStrictNullComparison
{
    /**
     * @return Collection
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
                    'apellidos' => mb_strtoupper(trim($user->surname)),
                    'nombre' => mb_strtoupper(trim($user->name)),
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
            'Horas de asistencia',
        ];
    }
}
