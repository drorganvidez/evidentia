<?php

namespace App\Http\Services;

use App\Models\Meeting;

class MeetingService extends Service
{

    public function __construct()
    {
        parent::__construct(Meeting::class);

        $this->validation_rules = [
            'title' => ['required', 'string'],
            'datetime' => ['required', 'string'],
            'place' => ['required', 'string'],
            'type' => ['required', 'numeric'],
            'modality' => ['required', 'numeric'],
            'hours' => ['required', 'numeric']
        ];

    }

}
