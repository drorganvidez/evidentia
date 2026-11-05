<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ?Model
    {
        if (! array_filter($row)) {
            return null;
        }

        return new User([
            'surname' => trim($row['apellidos']),
            'name' => trim($row['nombre']),
            'username' => trim($row['uvus']),
            'password' => Hash::make(\Random::getRandomIdentifier(16)),
            'email' => trim($row['email']),
            'clean_name' => \StringUtilites::clean(trim($row['nombre'])),
            'clean_surname' => \StringUtilites::clean(trim($row['apellidos'])),
        ]);
    }
}
