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
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;

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

    public function create_issue($id)
    {
        $instance = \Instantiation::instance();
        $kanban = Kanban::find($id);
        $users = User::orderBy('surname')->get();

        return view('kanban.create_issue', ['route' => route('kanban.view.issue.new', ['instance' => $instance, 'id' =>$id]),
                                                 'users' => $users,
                                                 'instance' => $instance,
                                                 'kanban' => $kanban]);
    }


    public function new_issue(Request $request, $id)
    {

        $instance = \Instantiation::instance();

        $request->validate([
            'title' => 'required|min:5|max:255',
            'description' => ['required',new MinCharacters(10),new MaxCharacters(20000)],
            'users' => 'required|array|min:1',
            'estimated_hours' => ['numeric','sometimes','min:0','max:99']
        ]);

        // datos necesarios para crear issue
        $user = Auth::user();

        // creación de un nuevo
        $issue = Issue::create([
            'user_id'=>$user->$id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'estimated_hours'=> $request->input('estimated_hours'),
            'kanban_id'=>$id
        ]);

        // Asociamos los usuarios a la issue
        $users_ids = $request->input('users',[]);

        foreach($users_ids as $user_id)
        {

            $user = User::find($user_id);
            $issue->users()->attach($user);

        }

        $issue->save();

        return redirect()->route('kanban.view',$instance)->with('success', 'Tarea creada con éxito.');

    }

}