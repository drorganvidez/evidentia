<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;

class ManagementStudentExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function user_list()
    {
        $users = User::all();

        // el presidente no puede editar los usuarios de tipo profesor
        $filtered_users = collect();
        $users->each(function ($item, $key) use ($filtered_users) {
            if (!$item->hasRole('LECTURE')) {
                $filtered_users->push($item);
            }
        });

        return $filtered_users;
    }
    public function collection()
    {
        $students= ManagementStudentExport::user_list();
        $res = collect();
        foreach($students as $student){

            if(Auth::User()->hasRole('PRESIDENT') or Auth::User()->hasRole('LECTURE')) {
                $roles = '';
                foreach ($student->roles as $rol){
                    $roles = $roles . $rol ->slug.', ';

                }
                $roles = substr($roles, 0,-2);
                $array = [
                    'DNI' => strtoupper(trim($student->title)),
                    'Apellidos' => strtoupper(trim($student->name)),
                    'Nombre' => strtoupper(trim($student->surname)),
                    'UVUS' => strtoupper(trim($student->username)),
                    'Roles' => strtoupper($roles)

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
            'Dni',
            'Apellidos',
            'Nombre',
            'UVUS',
            'Roles'

        ];
    }
}
