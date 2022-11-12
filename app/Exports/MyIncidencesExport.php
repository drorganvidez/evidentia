<?php

namespace App\Exports;

use App\Models\Incidence;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MyIncidencesExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $incidencias = Incidence::where("user_id","=",Auth::id(), "and", "status","=","CLOSED")->get();

        $res = collect();
        foreach($incidencias as $incidencia){

            if(Auth::User()->hasRole('STUDENT')) {

                $array = [
                    'Titulo' => strtoupper(trim($incidencia->title)),
                    'Comité' => strtoupper(trim($incidencia->comittee->name)),
                    'Creada' =>  strtoupper(trim($incidencia->created_at)),
                    'Estado' =>  strtoupper(trim($incidencia->status))

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
            'Comité',
            'Creada',
            'Estado'
        ];
    }
}
