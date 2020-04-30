<?php

namespace App\Http\Controllers;

use App\Evidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function coordinator_search_evidences(Request $request)
    {
        $s = $request->s;
        $instance = \Instantiation::instance();
        $comittee_id = Auth::user()->coordinator->comittee->id;

        // buscar evidencias por titulo
        $evidences = Evidence::where(['title' => $s])->get();
        //$evidences = Evidence::whereRaw('lower(title) like (?) and comittee_id = '.$comittee_id, ["%{$s}%"])->get();

        // buscar evidencias por horas
        $evidences = $evidences->concat(Evidence::whereRaw('lower(hours) like (?)', ["%{$s}%"])->get());

        // filtramos evidencias repetidos
        $evidences = $evidences->unique();

        return $evidences;

        $evidences = $evidences->paginate(5);


        return view('evidence.coordinator.list',
            ['instance' => $instance, 'evidences' => $evidences, 's' => $s]);
    }
}
