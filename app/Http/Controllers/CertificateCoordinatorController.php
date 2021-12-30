<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Models\ReasonRejection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateCoordinatorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:COORDINATOR|SECRETARY');
    }

    /****************************************************************************
     * MANAGE EVIDENCES
     ****************************************************************************/

    public function all()
    {
        $instance = \Instantiation::instance();

        $coordinator = Auth::user()->coordinator;
        $comittee = $coordinator->comittee;
        $evidences = $comittee->evidences_not_draft()->paginate(10);;

        return view('certificate.create',
            ['instance' => $instance, 'evidences' => $evidences]);
    }
}
