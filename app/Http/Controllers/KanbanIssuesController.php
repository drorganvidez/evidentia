<?php

namespace App\Http\Controllers;

use App\Exports\MyAttendeesExport;
use App\Models\KanbanIssues;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class KanbanIssuesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:STUDENT|COORDINATOR');
    }

    public function table()
    {
        $instance = \Instantiation::instance();
        $issues = KanbanIssues::where(['user_id' => Auth::id()])->get();

        return view('kanban.table',
            ['instance' => $instance, 'issues' => $issues]); //
    }
      

}
