<?php

namespace App\Http\Controllers;

use App\DefaultList;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DefaultListSecretaryController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:SECRETARY');
    }

    public function list()
    {
        $instance = \Instantiation::instance();

        $defaultlists = Auth::user()->secretary->default_lists()->paginate(5);

        return view('defaultlist.list',
            ['instance' => $instance, 'defaultlists' => $defaultlists]);
    }

    public function create()
    {
        $instance = \Instantiation::instance();

        $users = User::orderBy('surname')->get();

        return view('defaultlist.createandedit',
            ['instance' => $instance, 'users' => $users, 'route' => route('secretary.defaultlist.new',$instance)]);
    }

    public function new(Request $request)
    {

        $instance = \Instantiation::instance();

        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'users' => 'required|array|min:1',
        ]);

        $users_ids = $request->input('users',[]);

        $secretary = Auth::user()->secretary;

        $defaultlist = DefaultList::create([
            'name' => $request->input('name'),
            'secretary_id' => $secretary->id
        ]);


        $defaultlist->save();

        foreach($users_ids as $user_id)
        {

            $user = User::find($user_id);
            $defaultlist->users()->attach($user);

        }

        return redirect()->route('secretary.defaultlist.list',$instance)->with('success', 'Lista creada con éxito.');

    }

    public function edit($instance,$id)
    {

        $instance = \Instantiation::instance();
        $defaultlist = DefaultList::find($id);
        $users = User::orderBy('surname')->get();

        return view('defaultlist.createandedit',
            ['instance' => $instance, 'defaultlist' => $defaultlist,
                'users' => $users, 'route' => route('secretary.defaultlist.save',$instance), 'edit' => true]);

    }

    public function save(Request $request)
    {

        $instance = \Instantiation::instance();

        $validatedData = $request->validate([
            'name' => 'required|max:255',
        ]);

        $users_ids = $request->input('users',[]);
        $defaultlist = DefaultList::find($request->_id);

        $defaultlist->name = $request->input('name');
        $defaultlist->save();

        // eliminamos usuarios antiguos
        foreach($defaultlist->users as $user)
        {
            $defaultlist->users()->detach($user);
        }

        // agregamos los usuarios nuevos
        foreach($users_ids as $user_id)
        {
            $user = User::find($user_id);
            $defaultlist->users()->attach($user);
        }

        return redirect()->route('secretary.defaultlist.list',$instance)->with('success', 'Lista editada con éxito.');

    }

    public function remove(Request $request)
    {
        $defaultlist = DefaultList::find($request->_id);
        $instance = \Instantiation::instance();

        $defaultlist->delete();

        return redirect()->route('secretary.defaultlist.list',$instance)->with('success', 'Lista eliminada con éxito.');
    }
}
