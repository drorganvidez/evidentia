<?php

namespace App\Http\Controllers;

use App\Models\Comittee;
use App\Models\Kanban;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class KanbanController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:PRESIDENT|COORDINATOR|REGISTER_COORDINATOR|SECRETARY|STUDENT');
    }


    public function view($instance,$id)
    {
        $instance = \Instantiation::instance();
        $kanban = Kanban::find($id);

        $issues = Issue::where(['kanban_id' => $id])->get();

        return view('kanban.view',
            ['instance' => $instance, 'kanban' => $kanban, 'issues' =>$issues]);
    }

    public function list()
    {
        $kanban = Kanban::where(['user_id' => Auth::id(),'last' => true])->get();
        $instance = \Instantiation::instance();

        $kanban = $kanban->reverse();

        return view('kanban.list',
            ['instance' => $instance, 'kanban' => $kanban]);
    }

    /****************************************************************************
     * CREATE A KANBAN
     ****************************************************************************/

    public function create()
    {
        $instance = \Instantiation::instance();
        $comittees = Comittee::all();

        return view('kanban.create', ['route' => route('kanban.new',$instance),
                                      'instance' => $instance,
                                      'comittees' => $comittees]);
    }

    public function new(Request $request)
    {

        $instance = \Instantiation::instance();

        $request->validate([
            'title' => 'required|min:5|max:255'
        ]);

        // datos necesarios para crear tableros
        $user = Auth::user();

        // creación de un nuevo
        $kanban = Kanban::create([
            'title' => $request->input('title'),
            'user_id' => $user->id,
            'comittee_id' => $request->input('comittee')
        ]);

        // cómputo del sello
        //$kanban = \Stamp::compute_evidence($evidence);
        $kanban->save();

        return redirect()->route('kanban.list',$instance)->with('success', 'Tablero creado con éxito.');

    }

    /****************************************************************************
     * CREATE AN ISSUE
     ****************************************************************************/

    public function create_issue($instance, $id)
    {
        $instance = \Instantiation::instance();
        $kanban = Kanban::find($id);

        return view('kanban.view.issue.create', ['route' => route('kanban.view.issue.new', $instance),
                                    'instance' => $instance,
                                    'kanban' => $kanban]);
    }


    public function new_issue(Request $request,$id)
    {

        $instance = \Instantiation::instance();

        $request->validate([
            'title' => 'required|min:5|max:255',
            'description' => ['required',new MinCharacters(10),new MaxCharacters(20000)],
            'estimated_hours' => ['required_without:minutes','nullable','numeric','sometimes','max:99',new CheckHoursAndMinutes($request->input('minutes'))]
        ]);

        // datos necesarios para crear issue
        $user = Auth::user();

        // creación de un nuevo
        $issue = Issue::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'user_id' => $user->id,
            'estimated_hours'=> $request->input('estimated_hours'),
            'status'=>$request->input('status'),
            'kanban_id'=>$id
        ]);

        // cómputo del sello
        //$kanban = \Stamp::compute_evidence($evidence);
        $issue->save();

        return redirect()->route('kanban.view',$instance)->with('success', 'Tarea creada con éxito.');

    }

}