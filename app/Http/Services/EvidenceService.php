<?php

namespace App\Http\Services;

use App\Http\Resources\EvidenceResource;
use App\Models\Evidence;
use App\Rules\CheckHoursAndMinutes;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;

class EvidenceService extends Service
{

    public function __construct()
    {
        parent::__construct(Evidence::class, EvidenceResource::class);

        $validation_rules = [
            'title' => 'required|min:5|max:255',
            'hours' => ['required_without:minutes','nullable','numeric','sometimes','max:99',new CheckHoursAndMinutes(request()->input('minutes'))],
            'minutes' => ['required_without:hours','nullable','numeric','sometimes','max:60',new CheckHoursAndMinutes(request()->input('hours'))],
            //'description' => ['required',new MinCharacters(10),new MaxCharacters(20000)],
        ];

        $this->set_validation_rules($validation_rules);

    }

    public function calculate_stamp($id)
    {
        $evidence = Evidence::find($id);
        $evidence = \Stamp::compute_evidence($evidence);
        $evidence->save();
    }


}