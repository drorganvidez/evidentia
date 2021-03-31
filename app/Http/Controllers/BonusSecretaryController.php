<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BonusSecretaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:SECRETARY');
    }

    public function list()
    {
        $instance = \Instantiation::instance();

        $bonus = Auth::user()->secretary->comittee->bonus()->get();

        return view('bonus.list',
            ['instance' => $instance, 'bonus' => $bonus]);
    }

    public function create()
    {
        $instance = \Instantiation::instance();

        $users = User::orderBy('surname')->get();
        $defaultlists = Auth::user()->secretary->default_lists;

        return view('bonus.createandedit',
            ['instance' => $instance, 'users' => $users, 'defaultlists' => $defaultlists, 'route' => route('secretary.bonus.new',$instance)]);
    }

    public function new(Request $request)
    {

        $instance = \Instantiation::instance();

        $request->validate([
            'reason' => 'required|min:5|max:255',
            'hours' => 'required|numeric|between:0.5,99.99|max:100',
            'users' => 'required|array|min:1'
        ]);

        $bonus = Bonus::create([
            'reason' => $request->input('reason'),
            'hours' => $request->input('hours')
        ]);

        $bonus->comittee()->associate(Auth::user()->secretary->comittee);

        $bonus->save();

        // Asociamos los usuarios a la reunión
        $users_ids = $request->input('users',[]);

        foreach($users_ids as $user_id)
        {

            $user = User::find($user_id);
            $bonus->users()->attach($user);

        }

        return redirect()->route('secretary.bonus.list',$instance)->with('success', 'Bono de horas creado con éxito.');

    }

    public function edit($instance,$id)
    {
        $bonus = Bonus::find($id);
        $users = User::orderBy('surname')->get();
        $defaultlists = Auth::user()->secretary->default_lists;

        return view('bonus.createandedit',
            ['instance' => $instance, 'bonus' => $bonus, 'edit' => true, 'users' => $users, 'defaultlists' => $defaultlists, 'route' => route('secretary.bonus.save',$instance)]);
    }

    public function save(Request $request)
    {

        $instance = \Instantiation::instance();

        $request->validate([
            'reason' => 'required|min:5|max:255',
            'hours' => 'required|numeric|between:0.5,99.99|max:100'
        ]);

        $bonus = Bonus::find($request->_id);
        $bonus->reason = $request->input('reason');
        $bonus->hours = $request->input('hours');

        $bonus->save();

        // Asociamos los usuarios a la reunión
        $users_ids = $request->input('users',[]);

        // eliminamos usuarios antiguos del bono
        foreach($bonus->users as $user)
        {
            $bonus->users()->detach($user);
        }

        // agregamos los usuarios nuevos del bono
        foreach($users_ids as $user_id)
        {
            $user = User::find($user_id);
            $bonus->users()->attach($user);
        }

        return redirect()->route('secretary.bonus.list',$instance)->with('success', 'Bono editado con éxito.');

    }

    public function remove(Request $request)
    {
        $bonus = Bonus::find($request->_id);
        $instance = \Instantiation::instance();

        $bonus->delete();

        return redirect()->route('secretary.bonus.list',$instance)->with('success', 'Bono eliminado con éxito.');
    }


}
