<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RandomizeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function randomize()
    {

        $route = route('randomize.save');
        $selected = Evidence::where('rand', true)->count();

        return view('randomize.randomize', ['route' => $route, 'selected' => $selected]);
    }

    public function randomize_save()
    {

        DB::transaction(function () {
            // eliminamos la aleatorización previa (si la hubiera)
            $previousEvidences = Evidence::where('rand', '=', '1')->get();
            $previousEvidences->each(function ($item, $key) {
                $item->rand = false;
                $item->save();
            });

            $users = User::all();
            $count = 0;

            foreach ($users as $user) {

                try {

                    $evidence = $user->evidencesAccepted()->random();
                    $evidence->rand = true;
                    $evidence->save();
                    $count++;

                } catch (\Exception $e) {

                }
            }

            Activity::record(ActivityAction::RandomizeRun, details: ['count' => $count]);
        });

        return redirect()->route('randomize.randomize')->with('success', 'Evidencias aleatorizadas con éxito.');

    }
}
