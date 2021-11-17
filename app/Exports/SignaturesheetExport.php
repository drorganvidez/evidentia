<?php

namespace App\Exports;

use App\Models\SignatureSheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SignaturesheetExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $signatures = SignatureSheet::where("secretary_id","=",Auth::User()->secretary->id)->get();
        $res = collect();
        foreach($signatures as $signature){
            if($signature->meeting_request) {
                $convocatoria = $signature->meeting_request->title;
            } else {
                $convocatoria = "Sin asociar";
            }

            if(Auth::User()->hasRole('SECRETARY')) {

                $array = [
                    'Titulo' => strtoupper(trim($signature->title)),
                    'Convocatoria' => strtoupper(trim($convocatoria)),
                    'Ultima_modificacion' => strtoupper(trim($signature->updated_at)),
                    'URL_para_firmar' =>  URL::to('/') . "/21/sign/$signature->random_identifier"
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
            'Convocatoria',
            'Última modificación',
            'URL para firmar'
        ];
    }
}
