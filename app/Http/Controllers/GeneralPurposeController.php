<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckRoles;
use App\Models\User;

class GeneralPurposeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':SECRETARY');
    }

    // usuarios para los selectores de asistentes de los secretarios: solo lo necesario para mostrarlos
    public function users_all()
    {
        return User::orderBy('surname')->orderBy('name')->get(['id', 'name', 'surname']);
    }
}
