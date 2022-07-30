<?php

namespace App\Http\Controllers;

use App\Http\Resources\EvidenceResource;
use App\Http\Services\EvidenceService;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Proof;
use App\Rules\CheckHoursAndMinutes;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EvidenceController extends Controller
{

    public $evidence_service;

    public function __construct()
    {
        $this->evidence_service = new EvidenceService();
        $this->middleware('auth');
        $this->middleware('checkroles:PRESIDENT|COORDINATOR|REGISTER_COORDINATOR|SECRETARY|STUDENT');
    }

    /****************************************************************************
     * CREATE AN EVIDENCE
     ****************************************************************************/

    public function create()
    {
        $instance = \Instantiation::instance();
        $committees = Committee::all();

        $evidence_temp = Evidence::where([
            'user_id' => Auth::id(),
            'temp' => true,
            'status' => null
        ])->first();

        if($evidence_temp == null){
            $evidence_temp = Evidence::create([
                'user_id' => Auth::id(),
                'temp' => true,
                'last' => false
            ]);
        }

        return view('evidences.createandedit', [
            'route_draft' => route('evidences.create.draft',$instance),
            'route_publish' => route('evidences.create.publish',$instance),
            'instance' => $instance,
            'evidence_temp' => $evidence_temp,
            'evidence_temp_id' => $evidence_temp->id,
            'committees' => $committees]);

    }

    public function create_draft(Request $request)
    {
        return $this->create_and_edit($request,"DRAFT");
    }

    public function create_publish(Request $request)
    {
        return $this->create_and_edit($request,"PENDING");
    }

    private function create_and_edit($request, $status)
    {

        $this->evidence_service->validate();

        $data = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'hours' => $request->input('hours') + floor(($request->input('minutes')*100)/60)/100,
            'status' => $status,
            'temp' => false,
            'user_id' => Auth::id(),
            'committee_id' => $request->input('committee_id')
        ];

        // we update because we had previously assigned a temporary evidence with a permanent ID
        $evidence_json = $this->evidence_service->update($request->input('_id'), $data);

        // calculate stamp
        $evidence = $this->evidence_service->entity($evidence_json);
        $this->evidence_service->calculate_stamp($evidence->id);

        // change last (if we've editing this evidence)
        if(!empty($request->input('points_to'))){
            $this->change_last($request);
        } else {
            $evidence->last = true;
            $evidence->save();
        }

        if($status == 'DRAFT'){
            return redirect()->route('evidences.draft',\Instantiation::instance())->with('success', 'Evidencia guardada como borrador.');
        }

        if($status == 'PENDING'){
            return redirect()->route('evidences.pending',\Instantiation::instance())->with('success', 'Evidencia publicada y pendiente de revisar.');
        }

    }

    /****************************************************************************
     * EDIT AN EVIDENCE
     ***************************************************************************
     * @throws \ReflectionException
     */

    public function edit($instance, $id)
    {

        $committees = Committee::all();
        $evidence = Evidence::find($id);

        $evidence_temp = Evidence::where([
            'points_to' => $evidence->id,
            'temp' => true
        ])->first();

        if($evidence_temp == null){
            $evidence_temp = $this->clone_evidence($evidence);
            $this->copy_files($evidence, $evidence_temp);
        }

        return view('evidences.createandedit', [
            'route_draft' => route('evidences.edit.draft',$instance),
            'route_publish' => route('evidences.edit.publish',$instance),
            'instance' => $instance,
            'evidence_temp' => $evidence_temp,
            'evidence_temp_id' => $evidence_temp->id,
            'committees' => $committees
        ]);
    }

    public function edit_draft(Request $request){
        return $this->create_and_edit($request,"DRAFT");
    }

    public function edit_publish(Request $request){
        return $this->create_and_edit($request,"PENDING");
    }

    private function change_last($request){
        $evidence_source = Evidence::find($request->input('points_to'));
        $evidence_target = Evidence::find($request->input('_id'));

        $evidence_source->last = false;
        $evidence_target->last = true;

        $evidence_source->save();
        $evidence_target->save();
    }

    /**
     * @throws \ReflectionException
     */
    private function clone_evidence($evidence)
    {
        $data = [
            'user_id' => Auth::id(),
            'title' => $evidence->title,
            'hours' => $evidence->hours,
            'committee_id' => $evidence->committee->id,
            'description' => $evidence->description,
            'status' => $evidence->status,
            'temp' => true,
            'autosaved' => false,
            'points_to' => $evidence->id,
            'last' => false
        ];

        $evidence_json = $this->evidence_service->create($data);

        return $this->evidence_service->entity($evidence_json);

    }

    private function copy_files($evidence_from, $evidence_to){

        $user = Auth::user();
        $instance = \Instantiation::instance();

        $proofs_folder_from = $instance.'/proofs/'.$user->username.'/evidence_'.$evidence_from->id;
        $proofs_folder_to = $instance.'/proofs/'.$user->username.'/evidence_'.$evidence_to->id;


        foreach ($evidence_from->proofs as $proof){

            $old_directory = $proofs_folder_from.'/'.$proof->file->name;
            $new_directory = $proofs_folder_to.'/'.$proof->file->name;

            try {
                Storage::copy($old_directory, $new_directory);
            } catch (\Exception $e) {

            }

            $new_file_entity = File::create([
                'name' => $proof->file->name,
                'type' => $proof->file->type,
                'route' => $new_directory,
                'size' => $proof->file->size,
            ]);

            $file_entity = \Stamp::compute_file($new_file_entity);
            $file_entity->save();

            Proof::create([
                'evidence_id' => $evidence_to->id,
                'file_id' => $file_entity->id
            ]);

        }

    }

    /****************************************************************************
     * DELETE AN EVIDENCE
     ****************************************************************************/

    public function delete(Request $request)
    {

        $id = $request->_id;
        $evidence = Evidence::findOrFail($id);
        $instance = \Instantiation::instance();

        $this->evidence_service->recursive_delete_evidence($evidence);

        return redirect()->route('evidences.draft',$instance)->with('success', 'Evidencia borrada con éxito.');
    }

    public function delete_autosaved(Request $request){

        $user = Auth::user();
        $evidence = Evidence::find($request->input('_id'));

        $this->evidence_service->delete_files($evidence);

        $points_to = $evidence->points_to;

        if($points_to != null){
            $evidence->delete();
            return redirect()->route('evidences.edit', [
                'instance' => \Instantiation::instance(),
                'id' => $points_to
            ]);
        }

        $evidence->delete();

        return redirect()->route('evidences.create', \Instantiation::instance());
    }

    /****************************************************************************
     * LIST EVIDENCES
     ***************************************************************************
     * @throws \ReflectionException
     */

    public function list_draft(Request $request)
    {

        $evidences_res = collect();
        $committee_query = $request->query('committee');

        if(!empty($committee_query) and $committee_query !== "*"){
            $evidences_res = $this->evidence_service->get_evidences_by_committee_and_status($committee_query, 'DRAFT');
        } else{
            $evidences_res = Auth::user()->evidences_draft();
        }

        $stringify = $this->evidence_service->stringify_collection($evidences_res);

        return view('evidences.draft', ['evidences' => $stringify, 'committees' => Committee::all()]);
    }

    /**
     * @throws \ReflectionException
     */
    public function list_pending(Request $request)
    {
        $evidences_res = collect();
        $committee_query = $request->query('committee');

        if(!empty($committee_query) and $committee_query !== "*"){
            $evidences_res = $this->evidence_service->get_evidences_by_committee_and_status($committee_query, 'PENDING');
        } else{
            $evidences_res = Auth::user()->evidences_pending();
        }

        $stringify = $this->evidence_service->stringify_collection($evidences_res);

        return view('evidences.pending', ['evidences' => $stringify, 'committees' => Committee::all()]);
    }

    public function list_accepted(Request $request)
    {
        $evidences_res = collect();
        $committee_query = $request->query('committee');

        if(!empty($committee_query) and $committee_query !== "*"){
            $evidences_res = $this->evidence_service->get_evidences_by_committee_and_status($committee_query, 'ACCEPTED');
        } else{
            $evidences_res = Auth::user()->evidences_accepted();
        }

        $stringify = $this->evidence_service->stringify_collection($evidences_res);

        return view('evidences.accepted', ['evidences' => $stringify, 'committees' => Committee::all()]);
    }

    /**
     * @throws \ReflectionException
     */
    public function list_rejected(Request $request)
    {
        $evidences_res = collect();
        $committee_query = $request->query('committee');

        if(!empty($committee_query) and $committee_query !== "*"){
            $evidences_res = $this->evidence_service->get_evidences_by_committee_and_status($committee_query, 'REJECTED');
        } else{
            $evidences_res = Auth::user()->evidences_rejected();
        }

        $stringify = $this->evidence_service->stringify_collection($evidences_res);

        return view('evidences.rejected', ['evidences' => $stringify, 'committees' => Committee::all()]);
    }
}
