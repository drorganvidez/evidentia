<?php

namespace App\Http\Controllers;

use App\Models\DefaultList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\CheckRoles;

class DefaultListSecretaryController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class . ':SECRETARY');
    }

    public function list()
    {
        
        $defaultlists = Auth::user()->secretary->defaultLists()->get();

        return view('defaultlist.list',
            ['defaultlists' => $defaultlists]);
    }

    public function create()
    {

        $users = User::orderBy('surname')->get();

        return view('defaultlist.createandedit',
            ['users' => $users, 'route' => route('secretary.defaultlist.new')]);
    }

    public function new(Request $request)
    {

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

        return redirect()->route('secretary.defaultlist.list')->with('success', 'Lista creada con éxito.');

    }

    public function edit($id)
    {

        
        $defaultlist = DefaultList::find($id);
        $users = User::orderBy('surname')->get();

        return view('defaultlist.createandedit',
            ['defaultlist' => $defaultlist,
                'users' => $users, 'route' => route('secretary.defaultlist.save'), 'edit' => true]);

    }

    public function save(Request $request)
    {

        

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

        return redirect()->route('secretary.defaultlist.list')->with('success', 'Lista editada con éxito.');

    }

    public function remove(Request $request)
    {
        $defaultlist = DefaultList::find($request->_id);
        

        $defaultlist->delete();

        return redirect()->route('secretary.defaultlist.list')->with('success', 'Lista eliminada con éxito.');
    }
}
