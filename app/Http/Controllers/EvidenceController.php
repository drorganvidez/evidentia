<?php

namespace App\Http\Controllers;


use App\Comittee;
use App\Evidence;
use App\File;
use App\Proof;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvidenceController extends Controller
{

    public function __construct()
    {
        $this->middleware('checkroles:PRESIDENT|COORDINATOR|REGISTER_COORDINATOR|SECRETARY|STUDENT');
    }

    public function list()
    {
        $evidences = Evidence::where('user_id', Auth::id())->orderBy('created_at', 'desc')->paginate(5);
        $instance = instance();
        return view('evidence.list',
            ['instance' => $instance, 'evidences' => $evidences]);
    }

    public function create()
    {
        $instance = instance();
        $comittees = Comittee::all();
        return view('evidence.create', ['route' => route('evidence.new',$instance),
                                            'instance' => $instance,
                                            'comittees' => $comittees]);
    }

    public function new(Request $request)
    {

        // validar una evidencia
        /*$request->validate([
            'title' => ['required', 'min:5', 'max:255'],
            'hours' => ['required', 'numeric', 'between:0.5,99.99', 'max:100'],
            'description' => ['required', 'min:10', 'max:20000']
        ]);*/

        // datos necesarios para crear evidencias
        $user = Auth::user();
        $instance = instance();

        // creación de una nueva evidencia
        $evidence = Evidence::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'hours' => $request->input('hours'),
            'stamp' => 'estoesunsello',
            'user_id' => $user->id,
            'comittee_id' => $request->input('comittee')
        ]);

        // cómputo del sello
        $evidence = \Stamp::compute_evidence($evidence);
        $evidence->save();

        // creación de la prueba o pruebas adjuntas
        $files = $request->file('files');
        foreach($files as $file){

            // almacenamos en disco la prueba
            $path = Storage::putFileAs($instance.'/proofs/'.$user->username.'/evidence_'.$evidence->id.'', $file, $file->getClientOriginalName());

            // almacenamos en la BBDD la información del archivo
            $file_entity = File::create([
                'name' => $file->getClientOriginalName(),
                'type' => strtolower($file->getClientOriginalExtension()),
                'route' => $path,
                'size' => $file->getSize(),
            ]);

            // cómputo del sello
            $file_entity = \Stamp::compute_file($file_entity);
            $file_entity->save();

            // almacenamos en la BBDD la información de la prueba de la evidencia
            $proof = Proof::create([
                'evidence_id' => $evidence->id,
                'file_id' => $file_entity->id
            ]);
        }

        return redirect()->route('evidence.list',$instance)->with('success', 'Evidencia creada con éxito.');

    }
}
