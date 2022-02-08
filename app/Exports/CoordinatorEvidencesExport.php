<?php

namespace App\Exports;

use App\Models\Evidence;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CoordinatorEvidencesExport implements FromCollection, WithHeadings
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
        $evidences = null;
        if($this->type=="all") {
            $coordinator = Auth::user()->coordinator;
            $comittee = $coordinator->comittee;
            $evidences = $comittee->evidences_not_draft()->get();
        } else if($this->type=="pending") {
            $coordinator = Auth::user()->coordinator;
            $comittee = $coordinator->comittee;
            $evidences = $comittee->evidences_pending()->get();
        } else if($this->type=="accepted") {
            $coordinator = Auth::user()->coordinator;
            $comittee = $coordinator->comittee;
            $evidences = $comittee->evidences_accepted()->get();
        } else if($this->type=="rejected") {
            $coordinator = Auth::user()->coordinator;
            $comittee = $coordinator->comittee;
            $evidences = $comittee->evidences_rejected()->get();
        }
        $res = collect();
        foreach ($evidences as $evidence) {

            if (Auth::User()->hasRole('COORDINATOR')) {
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
            'Nombre del autor',
            'Apellidos del autor',
            'Horas',
            'Comité',
            'Creada',
            'Estado'
        ];
    }

}
