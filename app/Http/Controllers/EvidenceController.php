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

    public function view($instance,$id)
    {
        $instance = \Instantiation::instance();
        $evidence = Evidence::find($id);

        return view('evidence.view',
            ['instance' => $instance, 'evidence' => $evidence]);
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

    private function copy_files($evidence_previous, $evidence_new, $removed_files)
    {
        $user = Auth::user();
        $instance = \Instantiation::instance();

        foreach($evidence_previous->proofs as $proof){

            $file = $proof->file;

            // los archivos que hemos "eliminado" de la evidencia anterior no se incluyen en la nueva
            $collection = Str::of($removed_files)->explode('|');
            if($collection->contains($file->id))
                continue;

            try {

                // copiamos el archivo en sí
                Storage::copy($file->route, $instance . '/proofs/' . $user->username . '/evidence_' . $evidence_new->id . '/' . $file->name . '.' . $file->type);

                // almacenamos en la BBDD la información del archivo
                $file_entity = File::create([
                    'name' => $file->name,
                    'type' => $file->type,
                    'route' => $file->route,
                    'size' => $file->size,
                ]);

                // cómputo del sello
                $file_entity = \Stamp::compute_file($file_entity);
                $file_entity->save();

                // almacenamos en la BBDD la información de la prueba de la evidencia
                $proof = Proof::create([
                    'evidence_id' => $evidence_new->id,
                    'file_id' => $file_entity->id
                ]);

            } catch (\Exception $e) {

            }
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

        // evidencia desde la que hemos decidido partir
        $evidence_previous = Evidence::find($request->_id);

        // creamos la nueva evidencia a partir de la seleccionada para editar
        $evidence_new = $this->new_evidence($request,$status);

        // evidencia cabecera en el flujo de ediciones (la última)
        $evidence_header = $evidence_previous->find_header_evidence();
        $evidence_header->last = false;
        $evidence_header->save();

        // apuntamos al final del flujo de ediciones
        $evidence_new->points_to = $evidence_header->id;
        $evidence_new->save();

        // nos traemos los archivos de la evidencia anterior y los copiamos
        $this->copy_files($evidence_previous,$evidence_new,$request->removed_files);

        // si el usuario decide meter archivos nuevos
        if($request->hasFile('files'))
            $this->new_files($request,$evidence_new);

        return redirect()->route('evidence.list',$instance)->with('success', 'Evidencia editada con éxito.');

    }

    /****************************************************************************
     * REMOVE AN EVIDENCE
     ****************************************************************************/

    public function remove(Request $request)
    {
        $id = $request->_id;
        $evidence = Evidence::find($id);
        $instance = \Instantiation::instance();

        // eliminamos recursivamente la evidencia y todas las versiones anteriores, incluyendo archivos
        $this->delete_evidence($evidence);

        return redirect()->route('evidence.list',$instance)->with('success', 'Evidencia borrada con éxito.');
    }

    private function delete_evidence($evidence)
    {
        $instance = \Instantiation::instance();
        $user = Auth::user();

        // por si la evidencia apunta a otra anterior
        $evidence_previous = Evidence::find($evidence->points_to);

        // eliminamos los archivos almacenados
        $this->delete_files($evidence);
        Storage::deleteDirectory($instance.'/proofs/'.$user->username.'/evidence_'.$evidence->id.'');
        $evidence->delete();

        if($evidence_previous != null)
        {
            $this->delete_evidence($evidence_previous);
        }
    }

    private function delete_files($evidence)
    {
        foreach($evidence->proofs as $proof)
        {
            $proof->file->delete();
        }
    }


}
