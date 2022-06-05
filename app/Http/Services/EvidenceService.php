<?php

namespace App\Http\Services;

use App\Models\Evidence;

class EvidenceService extends Service
{

    public function __construct()
    {
        parent::__construct(Evidence::class);

        $this->validation_rules = [
            'title' => 'required|min:5|max:255',
            'hours' => ['required_without:minutes','nullable','numeric','sometimes','max:99'],
            'minutes' => ['required_without:hours','nullable','numeric','sometimes','max:60'],
        ];


    }


}
