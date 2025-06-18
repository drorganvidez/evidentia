<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Models\User;

class RandomizeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function randomize()
    {

        $route = route('randomize.save');

        return view('randomize.randomize', ['route' => $route]);
    }

    public function randomize_save()
    {

        // eliminamos la aleatorización previa (si la hubiera)
        $previousEvidences = Evidence::where('rand', '=', '1')->get();
        $previousEvidences->each(function ($item, $key) {
            $item->rand = false;
            $item->save();
        });

        $users = User::all();

        foreach ($users as $user) {

            try {

                $filtered_evidences = $user->evidences->filter(function ($value, $key) {
                    return $value->status == 'ACCEPTED';
                });

                $evidence = $filtered_evidences->random();
                $evidence->rand = true;
                $evidence->save();

            } catch (\Exception $e) {

            }
        }

        return redirect()->route('randomize.randomize')->with('success', 'Evidencias aleatorizadas con éxito.');

    }
}
