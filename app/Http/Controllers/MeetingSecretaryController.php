<?php

namespace App\Http\Controllers;

use App\DefaultList;
use App\Meeting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingSecretaryController extends Controller
{
    public function list()
    {
        $instance = \Instantiation::instance();

        $meetings = Auth::user()->secretary->comittee->meetings()->paginate(5);

        return view('meeting.list',
            ['instance' => $instance, 'meetings' => $meetings]);
    }

    public function create()
    {
        $instance = \Instantiation::instance();

        $users = User::orderBy('surname')->get();
        $defaultlists = Auth::user()->secretary->default_lists;

        return view('meeting.createandedit',
            ['instance' => $instance, 'users' => $users, 'defaultlists' => $defaultlists, 'route' => route('secretary.meeting.new',$instance)]);
    }

    public function new(Request $request)
    {

        $instance = \Instantiation::instance();

        $validatedData = $request->validate([
            'title' => 'required|min:5|max:255',
            'type' => 'required|numeric|min:1|max:2',
            'hours' => 'required|numeric|between:0.5,99.99|max:100',
            'place' => 'required|min:5|max:255',
            'date' => 'required',
            'time' => 'required',
            'users' => 'required|array|min:1'
        ]);

        $meeting = Meeting::create([
            'title' => $request->input('title'),
            'hours' => $request->input('hours'),
            'type' => $request->input('type'),
            'place' => $request->input('place'),
            'datetime' => $request->input('date')." ".$request->input('time')
        ]);

        $meeting->comittee()->associate(Auth::user()->secretary->comittee);

        $meeting->save();

        // Asociamos los usuarios a la reunión
        $users_ids = $request->input('users',[]);


        foreach($users_ids as $user_id)
        {

            $user = User::find($user_id);
            $meeting->users()->attach($user);

        }

        return redirect()->route('secretary.meeting.list',$instance)->with('success', 'Reunión creada con éxito.');

    }

    public function defaultlist($instance,$id)
    {
        return DefaultList::find($id)->users;
    }
}
