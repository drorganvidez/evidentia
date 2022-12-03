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

        return view('kanban.createandedit', ['route_draft' => route('kanban.draft',$instance),
                                            'route_publish' => route('kanban.publish',$instance),
                                            'instance' => $instance,
                                            'comittees' => $comittees]);
    }

    public function draft(Request $request)
    {
        return $this->new($request,"DRAFT");
    }

    public function publish(Request $request)
    {
        return $this->new($request,"PENDING");
    }

    private function new($request,$status)
    {

        $instance = \Instantiation::instance();

        $kanban = $this->new_kanban($request,$status);

        $this->save_files($request,$kanban);

        return redirect()->route('kanban.list',$instance)->with('success', 'Tablero creado con éxito.');

    }

    private function new_kanban($request,$status)
    {

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

        return $kanban;
    }

}