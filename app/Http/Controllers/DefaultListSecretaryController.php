<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Http\Middleware\CheckRoles;
use App\Models\DefaultList;
use App\Models\User;
use App\Support\Changes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DefaultListSecretaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':SECRETARY');
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
            'users.*' => 'integer|exists:users,id',
        ]);

        $users_ids = $request->input('users', []);

        $secretary = Auth::user()->secretary;

        DB::transaction(function () use ($request, $users_ids, $secretary) {
            $defaultlist = DefaultList::create([
                'name' => $request->input('name'),
                'secretary_id' => $secretary->id,
            ]);

            $defaultlist->save();

            foreach ($users_ids as $user_id) {

                $user = User::find($user_id);
                $defaultlist->users()->attach($user);

            }

            $after = $this->snapshot($defaultlist);
            Activity::record(ActivityAction::DefaultListCreated, $defaultlist, details: ['users' => count($after['users'])], targets: $after['users']);
        });

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
            'users' => 'array',
            'users.*' => 'integer|exists:users,id',
        ]);

        $users_ids = $request->input('users', []);
        $defaultlist = DefaultList::find($request->_id);
        $before = $this->snapshot($defaultlist);

        DB::transaction(function () use ($request, $users_ids, $defaultlist, $before) {
            $defaultlist->name = $request->input('name');
            $defaultlist->save();

            // eliminamos usuarios antiguos
            foreach ($defaultlist->users as $user) {
                $defaultlist->users()->detach($user);
            }

            // agregamos los usuarios nuevos
            foreach ($users_ids as $user_id) {
                $user = User::find($user_id);
                $defaultlist->users()->attach($user);
            }

            $after = $this->snapshot($defaultlist);
            Activity::record(ActivityAction::DefaultListEdited, $defaultlist, details: Changes::between($before, $after), targets: $after['users']);
        });

        return redirect()->route('secretary.defaultlist.list')->with('success', 'Lista editada con éxito.');

    }

    // nombre y miembros de una lista, tal como se anotan
    private function snapshot(DefaultList $defaultlist): array
    {
        return [
            'name' => $defaultlist->name,
            'users' => $defaultlist->users()->pluck('users.id')->map(fn ($id) => (int) $id)->sort()->values()->all(),
        ];
    }

    public function remove(Request $request)
    {
        $defaultlist = DefaultList::find($request->_id);
        $before = $this->snapshot($defaultlist);

        DB::transaction(function () use ($defaultlist, $before) {
            $defaultlist->delete();

            Activity::record(ActivityAction::DefaultListDeleted, $defaultlist, details: ['users' => count($before['users'])], subjectTitle: $before['name']);
        });

        return redirect()->route('secretary.defaultlist.list')->with('success', 'Lista eliminada con éxito.');
    }
}
