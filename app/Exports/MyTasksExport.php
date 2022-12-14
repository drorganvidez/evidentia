<?php

namespace App\Exports;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MyTasksExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $tasks = Task::where("user_id","=",Auth::id())->get();

        $res = collect();
        foreach($tasks as $task){

            if(Auth::User()->hasRole('STUDENT')) {

                $array = [
                    'Titulo' => strtoupper(trim($task->title)),
                    'Horas' => strtoupper(trim($task->hours)),
                    'Comité' => strtoupper(trim($task->comittee->name)),
                    'Fecha de inicio' => strtoupper(trim($task->start_date)),
                    'Fecha fin' => strtoupper(trim($task->end_date)),
                    'Creada' =>  strtoupper(trim($task->created_at))
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
            'Fecha de inicio',
            'Fecha fin',
            'Creada'
        ];
    }
}
