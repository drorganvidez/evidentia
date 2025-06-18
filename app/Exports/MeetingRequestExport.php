<?php

namespace App\Exports;

use App\Models\MeetingRequest;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;

class MeetingRequestExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $meeting_requests= MeetingRequest::where("secretary_id","=",Auth::User()->secretary->id)->get();
        $res = collect();
        foreach($meeting_requests as $request){

            if(Auth::User()->hasRole('SECRETARY')) {

                $array = [
                    'Titulo' => strtoupper(trim($request->title)),
                    'Programado_para' => strtoupper(trim($request->datetime)),
                    'Ultima_modificacion' => strtoupper(trim($request->updated_at))

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
            'Programada para',
            'Última modificación'

        ];
    }

}
