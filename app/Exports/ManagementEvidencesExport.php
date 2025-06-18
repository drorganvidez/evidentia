<?php

namespace App\Exports;

use App\Models\Evidence;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ManagementEvidencesExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $evidences = Evidence::all();
        $res = collect();
        foreach ($evidences as $evidence) {

            if (Auth::User()->hasRole('LECTURE') or Auth::User()->hasRole('PRESIDENT')) {

                $array = [
                    'Título' => strtoupper(trim($evidence->title)),
                    'Nombre_autor' => strtoupper(trim($evidence->user->name)),
                    'Apellidos_autor' => strtoupper(trim($evidence->user->username)),
                    'Horas' => strtoupper(trim($evidence->hours)),
                    'Comite' => strtoupper(trim($evidence->comittee->name)),
                    'Creada' => strtoupper(trim($evidence->created_at)),
                    'Estado' => trim($evidence->status)
                ];

                $object = (object)$array;
                $res->push($object);
            }
        }
        return $res;
    }

    public function headings(): array
    {
        return [
            'Título',
            'Nombre autor',
            'Apellidos autor',
            'Horas',
            'Comité',
            'Creada',
            'Estado'
        ];
    }

}
