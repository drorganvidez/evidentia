<?php

namespace App\Http\Controllers;


use App\Models\Kanban;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class IssueController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:PRESIDENT|COORDINATOR|REGISTER_COORDINATOR|SECRETARY|STUDENT');
    }

    // public function view($instance,$id)
    // {
    //     $instance = \Instantiation::instance();
    //     $issue = Issue::find($id);

    //     return view('issue.view',
    //         ['instance' => $instance, 'issue' => $issue]);
    // }

    // public function list()
    // {
    //     $issue = Issue::where(['user_id' => Auth::id(),'last' => true])->get();
    //     $instance = \Instantiation::instance();

    //     $issue = $issue->reverse();

    //     return view('kanban.view',
    //         ['instance' => $instance, 'issue' => $issue]);
    // }

    // public function create()
    // {
    //     $instance = \Instantiation::instance();

    //     $users = User::orderBy('surname')->get();

    //     return view('issue.create',
    //         ['instance' => $instance, 'users' => $users, 'route' => route('coordinator.issue.new',$instance)]);
    // }

    // public function new(Request $request)
    // {

    //     $instance = \Instantiation::instance();

    //     $request->validate([
    //         'title' => 'required|min:5|max:255',
    //         'description' => ['required',new MinCharacters(10),new MaxCharacters(20000)]
    //         'estimated_hours' => 'required|numeric|between:0.5,99.99|max:100',
    //         'users' => 'required|array|min:1'
    //     ]);

    //     $issue = Issue::create([
    //         'title' => $request->input('title'),
    //         'description' => $request->input('description'),
    //         'estimated_hours' => $request->input('estimated_hours'),
    //     ]);

    //     $issue->comittee()->associate(Auth::user()->coordinator->comittee);

    //     $issue->save();

    //     // Asociamos los usuarios a la reunión
    //     $users_ids = $request->input('users',[]);

    //     foreach($users_ids as $user_id)
    //     {

    //         $user = User::find($user_id);
    //         $bonus->users()->attach($user);

    //     }

    //     return redirect()->route('secretary.bonus.list',$instance)->with('success', 'Bono de horas creado con éxito.');

    // }

}