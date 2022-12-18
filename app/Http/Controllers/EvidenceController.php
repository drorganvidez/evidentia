<?php

namespace App\Http\Controllers;

use App\Exports\MyEvidencesExport;
use App\Models\Comittee;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Proof;
use App\Rules\CheckHoursAndMinutes;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

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

        /*lista de archivos segun el tipo*/

        $evidence_storaged_files_type = collect();
        foreach($evidence-> proofs as $proof){
            $evidence_storaged_files_type->push($proof->file->type);
        }
        $evidence_vp_type = collect();
        foreach($evidence->verified_proofs as $vp)
        {
            $evidence_vp_type->push($vp->type);
        }
        $evidence_storaged_files_type = $evidence_storaged_files_type->unique();
        $evidence_vp_type = $evidence_vp_type->unique();

        return view('evidence.view',
            ['instance' => $instance, 'evidence' => $evidence, 'dict_storaged_files' => $evidence_storaged_files_type, 'dict_vp_filetypes' => $evidence_vp_type]);
    }

    public function list()
    {
        $evidences = Evidence::where(['user_id' => Auth::id(),'last' => true])->get();
        $instance = \Instantiation::instance();

        $evidences = $evidences->reverse();

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

        $this->save_files($request,$evidence);

        return redirect()->route('evidence.list',$instance)->with('success', 'Evidencia creada con éxito.');

    }

    private function new_evidence($request,$status)
    {

        $request->validate([
            'title' => 'required|min:5|max:255',
            'hours' => ['required_without:minutes','nullable','numeric','sometimes','max:99',new CheckHoursAndMinutes($request->input('minutes'))],
            'minutes' => ['required_without:hours','nullable','numeric','sometimes','max:60',new CheckHoursAndMinutes($request->input('hours'))],
            'description' => ['required',new MinCharacters(10),new MaxCharacters(20000)],
        ]);

        // datos necesarios para crear evidencias
        $user = Auth::user();
        $minutes = $request->input('minutes');

        // creación de una nueva evidencia
        $evidence = Evidence::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'hours' => $request->input('hours') + floor(($minutes*100)/60)/100,
            'status' => $status,
            'user_id' => $user->id,
            'comittee_id' => $request->input('comittee')
        ]);

        // cómputo del sello
        $evidence = \Stamp::compute_evidence($evidence);
        $evidence->save();

        return $evidence;
    }

    private function save_files($request, $evidence)
    {
        $user = Auth::user();
        $instance = \Instantiation::instance();
        $token = $request->session()->token();
        $tmp = $instance.'/tmp/'.$user->username.'/'.$token.'/';

        foreach (Storage::files($tmp) as $filename) {

            $name = pathinfo($filename, PATHINFO_FILENAME);
            $type = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $size = Storage::size($filename);
            $old_directory = $filename;
            $new_directory = $instance.'/proofs/'.$user->username.'/evidence_'.$evidence->id.'/'.$name.'.'.$type;

            try{
                // movemos
                Storage::move($old_directory, $new_directory);

                // almacenamos en la BBDD la información del archivo
                $file_entity = File::create([
                    'name' => $name,
                    'type' => $type,
                    'route' => $new_directory,
                    'size' => $size,
                ]);

                // cómputo del sello
                $file_entity = \Stamp::compute_file($file_entity);
                $file_entity->save();

                // almacenamos en la BBDD la información de la prueba de la evidencia
                $proof = Proof::create([
                    'evidence_id' => $evidence->id,
                    'file_id' => $file_entity->id
                ]);
            } catch (\Exception $e) {

            }

        }

        // ya no necesitamos la carpeta temporal, la eliminamos
        Storage::deleteDirectory($tmp);

    }

    private function copy_files_into_temporary_folder($evidence)
    {

        $user = Auth::user();
        $instance = \Instantiation::instance();
        $token = session()->token();

        $proofs_folder = $instance.'/proofs/'.$user->username.'/evidence_'.$evidence->id;
        $tmp = $instance.'/tmp/'.$user->username.'/'.$token;

        foreach (Storage::files($proofs_folder) as $filename) {

            $filename_basename = pathinfo($filename, PATHINFO_BASENAME);

            $old_directory = $proofs_folder.'/'.$filename_basename;
            $new_directory = $tmp.'/'.$filename_basename;

            try {
                // copiamos
                Storage::copy($old_directory, $new_directory);
            } catch (\Exception $e) {

            }

        }

    }

    /****************************************************************************
     * EDIT AN EVIDENCE
     ****************************************************************************/

    public function edit($instance,$id)
    {

        $user = Auth::user();
        $instance = \Instantiation::instance();
        $token = session()->token();

        $evidence = Evidence::find($id);
        $comittees = Comittee::all();

        $tmp = $instance.'/tmp/'.$user->username.'/'.$token.'/';

        Storage::deleteDirectory($tmp);

        // generamos un nuevo token
        session()->regenerate();

        // copiamos las pruebas a una carpeta temporal para poder trabajar con los mismos
        $this->copy_files_into_temporary_folder($evidence);

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

        // copiamos ahora los archivos de la carpeta temporal a la nueva evidencia
        $this->save_files($request,$evidence_new);

        if($status == "DRAFT") {
            return redirect()->route('evidence.list', $instance)->with('success', 'Evidencia editada con éxito.');
        }else{
            return redirect()->route('evidence.list', $instance)->with('success', 'Evidencia publicada con éxito.');
        }

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
        foreach($evidence->verified_proofs as $proof)
        {
            $proof->delete();
        }
    }

    /****************************************************************************
     * REEDIT AN EVIDENCE
     ****************************************************************************/

    public function reedit(Request $request)
    {
        $id = $request->_id;
        $evidence = Evidence::find($id);
        $instance = \Instantiation::instance();

        $evidence->status = "DRAFT";

        $evidence->save();

        return redirect()->route('evidence.list',$instance)->with('success', 'Evidencia reasignada como borrador con éxito.');
    }


    public function export($instance, $ext)
    {
        try {
            ob_end_clean();
            if(!in_array($ext, ['csv', 'pdf', 'xlsx'])){
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new MyEvidencesExport(), 'misevidencias-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }


}
