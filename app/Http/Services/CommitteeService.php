<?php

namespace App\Http\Services;

use App\Models\Comittee;
use App\Models\Evidence;
use App\Models\User;
use App\Rules\CheckHoursAndMinutes;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;

class CommitteeService extends Service
{

    public function __construct()
    {
        parent::__construct(Comittee::class);

        $this->validation_rules = [
            'name' => ['required', 'string'],
            'icon' => ['required', 'string']
        ];


    }


}
