<?php

namespace App\Http\Controllers;

use App\Http\Services\EvidenceService;
use App\Http\Services\ReviewService;
use App\Http\Services\UserService;
use App\Models\Evidence;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class EvidenceCoordinatorController extends Controller
{

    public $evidence_service;
    public $user_service;
    public $review_service;

    public function __construct()
    {
        $this->evidence_service = new EvidenceService();
        $this->user_service = new UserService();
        $this->review_service = new ReviewService();
        $this->middleware('auth');
        $this->middleware('checkroles:COORDINATOR');
    }

    public function evidences_list()
    {
        $committee = Auth::user()->coordinator->committee;
        $status = request()->query('status');

        $evidences = collect();

        if(!empty($status)){
            $evidences = $this->evidence_service->get_all_evidences_by_committee_and_status($committee, $status);
        }else{
            $evidences = $this->evidence_service->get_all_evidences_by_committee($committee);
        }

        //$this->evidence_service->get_all_evidences_by_committee()


        $evidences_stringify = $this->evidence_service->stringify_collection($evidences);

        return view('coordinator.evaluation.list', [
            'evidences' => $evidences_stringify
        ]);
    }

    public function evidences_moderate()
    {

        $next_evidence = $this->next();

        $no_evidences = false;

        if($next_evidence == null){
            $no_evidences = true;
        }

        return view('coordinator.evaluation.moderation_mode', [
            'no_evidences' => $no_evidences
        ]);
    }

    public function evidences_moderate_p(Request $request)
    {
        $next_evidence = $this->next();
        $request->session()->flash('moderation');

        return redirect()->route('coordinator.evidences.moderate.evidence', [
            'instance' => \Instantiation::instance(),
            'id' => $next_evidence->id
        ]);
    }

    private function next()
    {
        $committee = Auth::user()->coordinator->committee;
        $evidences_pending = $this->evidence_service->get_all_pending_evidences_by_committee($committee);
        return $evidences_pending->first();
    }

    public function evidences_moderate_evidence($instance, $id)
    {
        $evidence = Evidence::findOrFail($id);
        $review = $evidence->review;

        return view('coordinator.evaluation.moderate', [
           'evidence' => $evidence,
            'review' => $review
        ]);
    }

    public function evidences_moderate_evidence_p(Request $request)
    {

        if($request->input('moderation') === 'true'){
            $request->session()->flash('moderation');
        }

        $this->review_service->validate();

        $evidence = Evidence::find($request->input('_id_evidence'));
        $review = Review::find($request->input('_id_review'));

        $status = $request->input('score') < 5 ? 'REJECTED' : 'ACCEPTED';

        $data = [
            'evidence_id' => $evidence->id,
            'score' => $request->input('score'),
            'comment' => $request->input('comment'),
            'status' => $status,
        ];

        if ($review === null){
            $this->review_service->create_review($data, $evidence, $status);
        }else{
            $this->review_service->update_review($review->id, $data, $evidence, $status);
        }

        if($request->input('moderation') === 'true'){

            $next_evidence = $this->next();

            if($next_evidence == null){
                return redirect()->route('coordinator.evidences.list', \Instantiation::instance())->with('success', 'Todas las evidencias pendientes moderadas con éxito');
            }else{
                return redirect()->route('coordinator.evidences.moderate.evidence', [
                    'instance' => \Instantiation::instance(),
                    'id' => $next_evidence->id
                ]);
            }

        }else{
            return redirect()->route('coordinator.evidences.list', \Instantiation::instance())->with('success', 'La evidencia se ha moderado con éxito');
        }


    }
}
