<?php

namespace App\Http\Controllers;

use App\Evidence;
use App\Http\Middleware\EvidenceCanBeEdited;
use App\User;
use Illuminate\Http\Request;

class RandomizeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function randomize()
    {
        $instance = \Instantiation::instance();
        $route = route('randomize.save',$instance);

        return view('randomize.randomize',['instance' => $instance, 'route' => $route]);
    }

    public function randomize_save()
    {
        $instance = \Instantiation::instance();

        // eliminamos la aleatorización previa (si la hubiera)
        $previous_evidences = Evidence::where('rand','=', '1')->get();
        $previous_evidences->each(function ($item, $key) {
            $item->rand = false;
            $item->save();
        });

        $users = User::all();

        foreach ($users as $user){

            try{

                $filtered_evidences = $user->evidences->filter(function ($value, $key) {
                    return $value->status == "ACCEPTED";
                });

                $evidence = $filtered_evidences->random();
                $evidence->rand = true;
                $evidence->save();

            }catch(\Exception $e){

            }
        }

        return redirect()->route('randomize.randomize', $instance)->with('success', 'Evidencias aleatorizadas con éxito.');

    }
}
