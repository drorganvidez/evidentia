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

    // public function publish(Request $request)
    // {
    //     return $this->new($request);
    // }

    private function new($request)
    {

        $instance = \Instantiation::instance();

        $this->new_kanban($request);

        return redirect()->route('kanban.list',$instance)->with('success', 'Tablero creado con éxito.');

    }

    private function new_kanban($request)
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