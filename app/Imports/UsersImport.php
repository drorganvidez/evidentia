<?php

namespace App\Imports;

use App\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if(!array_filter($row)) return null;

        return new User([
            'dni' => $row['dni'],
            'surname' => explode(',',$row['apellidos_nombre'])[0],
            'name' => explode(',',$row['apellidos_nombre'])[1],
            'username' => $row['uvus'],
            'password' => Hash::make($row['dni']),
            'email' => $row['correo'],
        ]);
    }
}
