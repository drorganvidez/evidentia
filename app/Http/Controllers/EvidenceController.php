<?php

namespace App\Http\Controllers;


use App\Comittee;
use App\Evidence;
use App\File;
use App\Proof;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
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
        $this->middleware('auth');
        $this->middleware('checkroles:PRESIDENT|COORDINATOR|REGISTER_COORDINATOR|SECRETARY|STUDENT');
    }

    public function list()
    {
        $evidences = Evidence::where(['user_id' => Auth::id(),'last' => true])->orderBy('created_at', 'desc')->paginate(5);
        $instance = \Instantiation::instance();

        return view('evidence.list',
            ['instance' => $instance, 'evidences' => $evidences]);
    }

    /****************************************************************************
     * CREATE AN EVIDENCE
     ****************************************************************************/

    public function create()
    {
        $instance = \Instantiation::instance();
        $comittees = Comittee::all();

        return view('evidence.createandedit', ['route_draft' => route('evidence.draft',$instance),
                                            'route_publish' => route('evidence.publish',$instance),
                                            'instance' => $instance,
                                            'comittees' => $comittees]);
    }

    public function draft(Request $request)
    {
        return $this->new($request,"DRAFT");
    }

    public function publish(Request $request)
    {
        return $this->new($request,"PENDING");
    }

    private function new($request,$status)
    {

        //return strlen(trim(strip_tags(trim($request->input('description')))));

        $instance = \Instantiation::instance();

        $evidence = $this->new_evidence($request,$status);

        $this->new_files($request,$evidence);

        return redirect()->route('evidence.list',$instance)->with('success', 'Evidencia creada con éxito.');

    }

    private function new_evidence($request,$status)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'hours' => 'required|numeric|between:0.5,99.99|max:100',
            'description' => ['required',new MinCharacters(10),new MaxCharacters(20000)],
        ]);

        // datos necesarios para crear evidencias
        $user = Auth::user();
        $instance = \Instantiation::instance();

        // creación de una nueva evidencia
        $evidence = Evidence::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'hours' => $request->input('hours'),
            'status' => $status,
            'user_id' => $user->id,
            'comittee_id' => $request->input('comittee')
        ]);

        // cómputo del sello
        $evidence = \Stamp::compute_evidence($evidence);
        $evidence->save();

        return $evidence;
    }

    private function new_files($request,$evidence)
    {
        $user = Auth::user();
        $instance = \Instantiation::instance();

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
    }

    /****************************************************************************
     * EDIT AN EVIDENCE
     ****************************************************************************/

    public function edit($instance,$id)
    {
        $evidence = Evidence::find($id);
        $comittees = Comittee::all();

        return view('evidence.createandedit', ['evidence' => $evidence, 'instance' => $instance,
            'comittees' => $comittees,
            'edit' => true,
            'route_draft' => route('evidence.draft.edit',$instance),
            'route_publish' => route('evidence.publish.edit',$instance)]);
    }

    public function draft_edit(Request $request)
    {
        return $this->save($request,"DRAFT");
    }

    public function publish_edit(Request $request)
    {
        return $this->save($request,"PENDING");
    }

    private function save($request,$status)
    {
        $instance = \Instantiation::instance();

        $evidence_previous = Evidence::find($request->_id);
        $evidence_previous->last = false;
        $evidence_previous->save();

        $evidence = $this->new_evidence($request,$status);
        $evidence->points_to = $request->_id;
        $evidence->save();

        return redirect()->route('evidence.list',$instance)->with('success', 'Evidencia editada con éxito.');

    }
}
