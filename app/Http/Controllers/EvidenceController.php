<?php

namespace App\Http\Controllers;

use App\Http\Resources\EvidenceResource;
use App\Http\Services\EvidenceService;
use App\Models\Committee;
use App\Models\Evidence;
use App\Rules\CheckHoursAndMinutes;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvidenceController extends Controller
{

    public $evidence_service;

    public function __construct()
    {
        $this->evidence_service = new EvidenceService();
        $this->middleware('auth');
        $this->middleware('checkroles:PRESIDENT|COORDINATOR|REGISTER_COORDINATOR|SECRETARY|STUDENT');
    }

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

    public function create_draft(Request $request)
    {
        return $this->new($request,"DRAFT");
    }

    public function create_publish(Request $request)
    {
        return $this->new($request,"PENDING");
    }

    private function new($request,$status)
    {

        $this->evidence_service->validate();

        $data = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'hours' => $request->input('hours') + floor(($request->input('minutes')*100)/60)/100,
            'status' => $status,
            'temp' => false,
            'user_id' => Auth::id(),
            'committee_id' => $request->input('committee_id')];

        // calculate stamp
        $evidence_json = $this->evidence_service->create($data);
        $evidence = $this->evidence_service->entity($evidence_json);
        $this->evidence_service->calculate_stamp($evidence->id);


        if($status == 'DRAFT'){
            return redirect()->route('evidences.draft',\Instantiation::instance())->with('success', 'Evidencia guardada como borrador.');
        }

        if($status == 'PENDING'){
            return redirect()->route('evidences.pending',\Instantiation::instance())->with('success', 'Evidencia publicada y pendiente de revisar.');
        }

    }

    public function list_draft()
    {

        $evidences_draft = Auth::user()->evidences_draft()->sortByDesc('created_at');

        $stringify = $this->evidence_service->stringify_collection($evidences_draft);

        return view('evidences.draft', ['evidences' => $stringify]);
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
