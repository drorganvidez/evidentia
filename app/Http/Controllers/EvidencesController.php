<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EvidencesController extends Controller
{
    public function create()
    {
        return view('evidences.create');
    }

    public function draft()
    {
        return view('evidences.draft');
    }

    public function pending()
    {
        return view('evidences.pending');
    }

    public function accepted()
    {
        return view('evidences.accepted');
    }

    public function rejected()
    {
        return view('evidences.rejected');
    }
}
