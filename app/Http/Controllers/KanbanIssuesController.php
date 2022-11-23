<?php

namespace App\Http\Controllers;

use App\Exports\MyAttendeesExport;
use App\Models\KanbanIssues;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Rules\CheckHoursAndMinutes;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;

class KanbanIssuesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:COORDINATOR');
    }

    public function table()
    {
        $instance = \Instantiation::instance();
        $issues = KanbanIssues::where(['user_id' => Auth::id()])->get();

        return view('kanban.table',
            ['instance' => $instance, 'issues' => $issues]); //
    }
      
    /****************************************************************************
     * CREATE AN ISSUE
     ****************************************************************************/

    public function create()
    {
        $instance = \Instantiation::instance();
        $users = User::all();

        return view('kanban.create', ['route_publish' => route('kanban.publish',$instance),
                                        'instance' => $instance,
                                        'users' => $users]);
    }

    public function publish(Request $request)
    {
        return $this->new($request,"PENDING");
    }

    private function new($request,$status)
    {

        $instance = \Instantiation::instance();

        $issue = $this->new_issue($request,$status);

        return redirect()->route('kanban.table',$instance)->with('success', 'Tarea creada con éxito.');

    }

    private function new_issue($request,$status)
    {

        $request->validate([
            'task' => 'required|min:5|max:255',
            'hours' => ['required_without:minutes','nullable','numeric','sometimes','max:99',new CheckHoursAndMinutes($request->input('minutes'))],
            'description' => ['required',new MinCharacters(10),new MaxCharacters(20000)]
        ]);

        // datos necesarios para crear tareas
        $user = Auth::user();

        // creación de una nueva tarea
        $issue = KanbanIssues::create([
            'task' => $request->input('task'),
            'description' => $request->input('description'),
            'hours' => $request->input('hours'),
            'type' => 'TODO',
            'user_id' =>  $request->input('user'),
            'comittee_id' => $user->coordinator->comittee->id
        ]);

        $issue->save();

        return $issue;
    }


}
