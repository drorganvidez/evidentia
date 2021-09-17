<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
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
            'dni' => trim($row['dni']),
            'surname' => trim($row['apellidos']),
            'name' => trim($row['nombre']),
            'username' => trim($row['uvus']),
            'password' => Hash::make(trim($row['dni'])),
            'email' => trim($row['email']),
            'clean_name' => \StringUtilites::clean(trim($row['nombre'])),
            'clean_surname' => \StringUtilites::clean(trim($row['apellidos'])),
        ]);
    }
}
