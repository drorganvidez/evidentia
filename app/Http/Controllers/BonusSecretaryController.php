<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Enums\ActivityFlag;
use App\Facades\Activity;
use App\Http\Middleware\CheckRoles;
use App\Models\Bonus;
use App\Models\User;
use App\Support\Changes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BonusSecretaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':SECRETARY');
    }

    public function list()
    {

        $bonus = Auth::user()->secretary->committee->bonus()->latest()->latest('id')->get();

        return view('bonus.list',
            ['bonus' => $bonus]);
    }

    public function create()
    {

        $users = User::orderBy('surname')->get();
        $defaultlists = Auth::user()->secretary->defaultLists;

        return view('bonus.createandedit',
            ['users' => $users, 'defaultlists' => $defaultlists, 'route' => route('secretary.bonus.new')]);
    }

    public function new(Request $request)
    {

        $request->validate([
            'reason' => 'required|min:5|max:255',
            'hours' => 'required|numeric|between:0.5,99.99|max:100',
            'users' => 'required|array|min:1',
            'users.*' => 'integer|exists:users,id',
        ]);

        DB::transaction(function () use ($request) {
            $bonus = Bonus::create([
                'reason' => $request->input('reason'),
                'hours' => $request->input('hours'),
            ]);

            $bonus->committee()->associate(Auth::user()->secretary->committee);

            $bonus->save();

            // Asociamos los usuarios al bono, sin repetidos
            $users_ids = $request->input('users', []);
            $bonus->users()->sync(array_unique(array_map('intval', $users_ids)));

            $after = $this->snapshot($bonus);
            Activity::record(
                ActivityAction::BonusCreated,
                $bonus,
                details: ['reason' => $after['reason'], 'hours' => $after['hours'], 'users' => count($after['users'])],
                flags: $this->overlap($after['users']),
                targets: $after['users'],
            );
        });

        return redirect()->route('secretary.bonus.list')->with('success', 'Bono de horas creado con éxito.');

    }

    public function edit($id)
    {
        $bonus = Bonus::find($id);
        $users = User::orderBy('surname')->get();
        $defaultlists = Auth::user()->secretary->defaultLists;

        return view('bonus.createandedit',
            ['bonus' => $bonus, 'edit' => true, 'users' => $users, 'defaultlists' => $defaultlists, 'route' => route('secretary.bonus.save')]);
    }

    public function save(Request $request)
    {

        $request->validate([
            'reason' => 'required|min:5|max:255',
            'hours' => 'required|numeric|between:0.5,99.99|max:100',
            'users' => 'array',
            'users.*' => 'integer|exists:users,id',
        ]);

        $bonus = Bonus::find($request->_id);
        $before = $this->snapshot($bonus);

        DB::transaction(function () use ($request, $bonus, $before) {
            $bonus->reason = $request->input('reason');
            $bonus->hours = $request->input('hours');

            $bonus->save();

            // Sustituimos los usuarios del bono, sin repetidos
            $users_ids = $request->input('users', []);
            $bonus->users()->sync(array_unique(array_map('intval', $users_ids)));

            $after = $this->snapshot($bonus);
            Activity::record(
                ActivityAction::BonusEdited,
                $bonus,
                details: Changes::between($before, $after),
                flags: $this->overlap($after['users']),
                targets: $after['users'],
            );
        });

        return redirect()->route('secretary.bonus.list')->with('success', 'Bono editado con éxito.');

    }

    // motivo, horas y beneficiarios de un bono, tal como se anotan
    private function snapshot(Bonus $bonus): array
    {
        return [
            'reason' => $bonus->reason,
            'hours' => (float) $bonus->hours,
            'users' => $bonus->users()->pluck('users.id')->map(fn ($id) => (int) $id)->sort()->values()->all(),
        ];
    }

    // un secretario que se da horas a sí mismo queda señalado
    private function overlap(array $users_ids): array
    {
        return in_array((int) Auth::id(), $users_ids, true) ? [ActivityFlag::RoleOverlap] : [];
    }

    public function remove(Request $request)
    {
        $bonus = Bonus::find($request->_id);
        $before = $this->snapshot($bonus);

        DB::transaction(function () use ($bonus, $before) {
            $bonus->delete();

            Activity::record(
                ActivityAction::BonusDeleted,
                $bonus,
                details: ['hours' => $before['hours'], 'users' => count($before['users'])],
                targets: $before['users'],
                subjectTitle: $before['reason'],
            );
        });

        return redirect()->route('secretary.bonus.list')->with('success', 'Bono eliminado con éxito.');
    }
}
