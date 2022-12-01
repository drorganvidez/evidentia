<?php

namespace App\Http\Services;

use App\Models\KanbanIssues;

class KanbanIssueService extends Service
{

    public function __construct()
    {
        parent::__construct(KanbanIssues::class);

        $this->validation_rules = [
            'task' => 'required|min:5|max:255',
            'hours' => ['nullable','numeric','sometimes','max:99'],
        ];


    }


}