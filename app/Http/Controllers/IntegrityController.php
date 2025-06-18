<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckRoles;
use App\Models\Evidence;
use App\Models\Proof;

class IntegrityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':LECTURE');
    }

    public function integrity()
    {

        $evidences = Evidence::all();
        $proofs = Proof::all();

        return view('integrity.list',
            ['evidences' => $evidences, 'proofs' => $proofs]);
    }
}
