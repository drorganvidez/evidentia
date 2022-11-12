<?php

namespace App\Exports;

use App\Models\Incidence;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CoordinatorIncidencesExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public $type = null;

    public function __construct($type){
        $this->type = $type;
    }

    public function collection()
    {
        $incidences = null;
        if($this->type=="all") {
            $coordinator = Auth::user()->coordinator;
            $comittee = $coordinator->comittee;
            $incidences = $comittee->incidences()->get();
        } else if($this->type=="pending") {
            $coordinator = Auth::user()->coordinator;
            $comittee = $coordinator->comittee;
            $incidences = $comittee->incidences_pending()->get();
        } else if($this->type=="inreview") {
            $coordinator = Auth::user()->coordinator;
            $comittee = $coordinator->comittee;
            $incidences = $comittee->incidences_in_review()->get();
        } else if($this->type=="closed") {
            $coordinator = Auth::user()->coordinator;
            $comittee = $coordinator->comittee;
            $incidences = $comittee->incidences_closed()->get();
        }
        $res = collect();
        foreach ($incidences as $incidence) {

            if (Auth::User()->hasRole('COORDINATOR')) {
                $array = [
                    'Título' => strtoupper(trim($incidence->title)),
                    'Nombre_autor' => strtoupper(trim($incidence->user->name)),
                    'Apellidos_autor' => strtoupper(trim($incidence->user->username)),
                    'Comite' => strtoupper(trim($incidence->comittee->name)),
                    'Creada' => strtoupper(trim($incidence->created_at)),
                    'Estado' => trim($incidence->status)
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
            'Nombre del autor',
            'Apellidos del autor',
            'Comité',
            'Creada',
            'Estado'
        ];
    }

}
