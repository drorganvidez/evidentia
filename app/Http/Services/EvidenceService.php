<?php

namespace App\Http\Services;

use App\Http\Resources\EvidenceResource;
use App\Models\Committee;
use App\Models\Evidence;
use App\Rules\CheckHoursAndMinutes;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EvidenceService extends Service
{

    public function __construct()
    {
        parent::__construct(Evidence::class, EvidenceResource::class);

        $this->rules = [
            'title' => 'required|min:5|max:255|unique:evidences',
            'hours' => ['required_without:minutes','nullable','numeric','sometimes','max:99',new CheckHoursAndMinutes(request()->input('minutes'))],
            'minutes' => ['required_without:hours','nullable','numeric','sometimes','max:60',new CheckHoursAndMinutes(request()->input('hours'))],
            'description' => ['required',new MinCharacters(10),new MaxCharacters(20000)],
        ];

    }

    public function calculate_stamp($id) : void
    {
        $evidence = Evidence::find($id);
        $evidence = \Stamp::compute_evidence($evidence);
        $evidence->save();
    }

    public function get_evidences_by_committee_and_status($committee_name, $status) : Collection
    {
        $committee = Committee::where(['name' => $committee_name])->first();

        if($committee == null){
            return collect();
        }

        return Evidence::where(['user_id' => Auth::id(), 'committee_id' => $committee->id, 'status' => $status])->get();

    }

    public function recursive_delete_evidence($evidence)
    {

        // por si la evidencia apunta a otra anterior
        $evidence_previous = Evidence::find($evidence->points_to);

        // we delete all files
        $this->delete_files($evidence);
        Storage::deleteDirectory(\Instantiation::instance().'/proofs/'.Auth::user()->username.'/evidence_'.$evidence->id);
        $evidence->delete();

        if($evidence_previous != null)
        {
            $this->recursive_delete_evidence($evidence_previous);
        }
    }

    public function delete_files($evidence)
    {
        foreach($evidence->proofs as $proof)
        {
            $proof->file->delete();
        }

    }

}