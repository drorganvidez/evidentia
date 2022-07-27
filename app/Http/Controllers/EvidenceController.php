<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\Evidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvidenceController extends Controller
{
    public function create()
    {
        $instance = \Instantiation::instance();
        $committees = Committee::all();

        $evidence_temp = Evidence::where(['user_id' => Auth::id(), 'temp' => true])->first();

        if($evidence_temp == null){
            $evidence_temp = Evidence::create([
                'user_id' => Auth::id(),
                'temp' => true
            ]);
        }

        return view('evidences.createandedit', [
            'route_draft' => route('evidences.create.draft',$instance),
            'route_publish' => route('evidences.create.publish',$instance),
            'instance' => $instance,
            'evidence_temp' => $evidence_temp,
            'committees' => $committees]);

    }

    public function list_draft()
    {
        return view('evidences.draft');
    }

    public function list_pending()
    {
        return view('evidences.pending');
    }

    public function list_accepted()
    {
        return view('evidences.accepted');
    }

    public function list_rejected()
    {
        return view('evidences.rejected');
    }
}
