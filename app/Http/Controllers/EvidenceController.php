<?php

namespace App\Http\Controllers;

use App\Http\Services\EvidenceService;
use App\Http\Services\UserService;
use App\Models\ApiToken;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Proof;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ReflectionException;

class EvidenceController extends Controller
{

    public $evidence_service;
    public $user_service;

    public function __construct()
    {
        $this->evidence_service = new EvidenceService();
        $this->user_service = new UserService();
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
        $students = $this->user_service->get_all_students_except_me();

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
            'committees' => $committees,
            'students' => $this->user_service->stringify_collection($students)
        ]);

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
            'committee_id' => $request->input('committee_id'),
            'guest_id' => $request->input('guest_id'),
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
     * @throws ReflectionException
     */

    public function edit($instance, $id)
    {

        $committees = Committee::all();
        $evidence = Evidence::find($id);
        $students = $this->user_service->get_all_students_except_me();

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
            'committees' => $committees,
            'students' => $this->user_service->stringify_collection($students)
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
     * @throws ReflectionException
     */
    private function clone_evidence($evidence): object
    {
        $data = [
            'user_id' => Auth::id(),
            'guest_id' => $evidence->guest_id,
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

        $evidence_json = $this->evidence_service->create($data, $validate = false);

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

    public function delete_autosaved(Request $request)
    {

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
     ****************************************************************************
     * @throws ReflectionException
     */

    public function list_draft(Request $request)
    {
        $committee_query = $request->query('committee');
        $evidences = $this->evidence_service->get_evidences_by_committee_and_status($committee_query, 'DRAFT');
        $stringify = $this->evidence_service->stringify_collection($evidences);

        return view('evidences.draft', ['evidences' => $stringify, 'committees' => Committee::all()]);
    }

    /**
     * @throws ReflectionException
     */
    public function list_pending(Request $request)
    {
        $committee_query = $request->query('committee');
        $evidences = $this->evidence_service->get_evidences_by_committee_and_status($committee_query, 'PENDING');
        $stringify = $this->evidence_service->stringify_collection($evidences);

        return view('evidences.pending', ['evidences' => $stringify, 'committees' => Committee::all()]);
    }

    public function list_accepted(Request $request)
    {
        $committee_query = $request->query('committee');
        $evidences = $this->evidence_service->get_evidences_by_committee_and_status($committee_query, 'ACCEPTED');
        $stringify = $this->evidence_service->stringify_collection($evidences);

        return view('evidences.accepted', ['evidences' => $stringify, 'committees' => Committee::all()]);
    }

    /**
     * @throws ReflectionException
     */
    public function list_rejected(Request $request)
    {
        $committee_query = $request->query('committee');
        $evidences = $this->evidence_service->get_evidences_by_committee_and_status($committee_query, 'REJECTED');
        $stringify = $this->evidence_service->stringify_collection($evidences);

        return view('evidences.rejected', ['evidences' => $stringify, 'committees' => Committee::all()]);
    }

    /****************************************************************************
     * LIST EVIDENCES
     ****************************************************************************
     */

    public function view($instance, $id)
    {
        $evidence = Evidence::findOrFail($id);
        return view('evidences.view', [
            'evidence' => $evidence
        ]);
    }

    /****************************************************************************
     * EXPORT EVIDENCES
     ****************************************************************************
     */

    public function export($instance, $id)
    {
        $evidence = Evidence::findOrFail($id);

        $this->evidence_service->zip_evidence($evidence);

        return $this->evidence_service->download_zip($evidence);
    }

    public function export_mass(Request $request)
    {

        $items_selected = $request->input("items_selected");

        $evidences = $this->evidence_service->get_collection_by_ids($items_selected);

        $zip_name = $this->evidence_service->zip_evidences($evidences);

        return $this->evidence_service->download_zip_by_name($zip_name);

    }

    /****************************************************************************
     * REASSIGN EVIDENCE
     ****************************************************************************
     */

    public function reassign($instance, $id)
    {
        $evidence = Evidence::findOrFail($id);
        $committees = Committee::where('id', '!=', $evidence->committee->id)->get();

        return view('evidences.reassign', [
            'instance' => $instance,
            'evidence' => $evidence,
            'committees' => $committees
        ]);

    }

    public function reassign_p(Request $request)
    {
        $evidence = Evidence::findOrFail($request->input('_id'));
        $committee = Committee::findOrFail($request->input('committee_id'));

        $evidence->committee_id = $committee->id;
        $evidence->save();

        return redirect()->route('evidences.pending', \Instantiation::instance())->with('success', 'La evidencia se ha reasignado de comité con éxito');
    }

}
