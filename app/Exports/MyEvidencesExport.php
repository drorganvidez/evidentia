<?php

namespace App\Exports;

use App\Models\Evidence;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MyEvidencesExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $evidencias = Evidence::where('user_id', '=', Auth::id())->get();

        $res = collect();
        foreach ($evidencias as $evidencia) {

            if (Auth::User()->hasRole('STUDENT')) {

                $array = [
                    'Titulo' => strtoupper(trim($evidencia->title)),
                    'Horas' => strtoupper(trim($evidencia->hours)),
                    'Comité' => strtoupper(trim($evidencia->comittee->name)),
                    'Creada' => strtoupper(trim($evidencia->created_at)),
                    'Estado' => strtoupper(trim($evidencia->status)),

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
            'Horas',
            'Comité',
            'Creada',
            'Estado',
        ];
    }
}
