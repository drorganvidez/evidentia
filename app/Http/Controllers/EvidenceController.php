<?php

namespace App\Http\Controllers;

use App\Exceptions\ProofNotStored;
use App\Http\Middleware\CheckRoles;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Proof;
use App\Rules\CheckHoursAndMinutes;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EvidenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':PRESIDENT,COORDINATOR,REGISTER_COORDINATOR,SECRETARY,STUDENT');
    }

    public function view($id)
    {
        $evidence = Evidence::find($id);

        return view('evidence.view',
            ['evidence' => $evidence]);
    }

    public function list()
    {
        $evidences = Evidence::where(['user_id' => Auth::id(), 'last' => true])->get();

        $evidences = $evidences->reverse();

        return view('evidence.list',
            ['evidences' => $evidences]);
    }

    /****************************************************************************
     * CREATE AN EVIDENCE
     ****************************************************************************/

    public function create()
    {

        $committees = Committee::all();

        return view('evidence.createandedit', ['route_draft' => route('evidence.draft'),
            'route_publish' => route('evidence.publish'),
            'committees' => $committees]);
    }

    public function draft(Request $request)
    {
        return $this->new($request, 'DRAFT');
    }

    public function publish(Request $request)
    {
        return $this->new($request, $this->published_status());
    }

    // nadie valida las evidencias de los presidentes: se aceptan al publicarlas
    private function published_status(): string
    {
        return Auth::user()->hasRole('PRESIDENT') ? 'ACCEPTED' : 'PENDING';
    }

    private function new($request, $status)
    {

        try {
            DB::transaction(function () use ($request, $status) {
                $evidence = $this->new_evidence($request, $status);

                $this->save_files($request, $evidence);
            });
        } catch (ProofNotStored) {
            return $this->proof_not_stored();
        }

        // ya no necesitamos la carpeta temporal, la eliminamos
        Storage::deleteDirectory($this->temporary_folder($request));

        return redirect()->route('evidence.list')->with('success', 'Evidencia creada con éxito.');

    }

    private function new_evidence($request, $status)
    {

        $request->validate([
            'title' => 'required|min:5|max:255',
            'hours' => ['required_without:minutes', 'nullable', 'numeric', 'sometimes', 'max:99', new CheckHoursAndMinutes($request->input('minutes'))],
            'minutes' => ['required_without:hours', 'nullable', 'numeric', 'sometimes', 'max:60', new CheckHoursAndMinutes($request->input('hours'))],
            'description' => ['required', new MinCharacters(10), new MaxCharacters(20000)],
            'committee' => 'required|exists:committees,id',
        ]);

        // datos necesarios para crear evidencias
        $user = Auth::user();
        $minutes = $request->input('minutes');

        // creación de una nueva evidencia
        $evidence = Evidence::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'hours' => $request->input('hours') + floor(($minutes * 100) / 60) / 100,
            'status' => $status,
            'user_id' => $user->id,
            'committee_id' => $request->input('committee'),
        ]);

        // cómputo del sello
        $evidence = \Stamp::compute_evidence($evidence);
        $evidence->save();

        return $evidence;
    }

    private function save_files($request, $evidence)
    {
        $user = Auth::user();

        foreach (Storage::files($this->temporary_folder($request)) as $filename) {

            $name = pathinfo($filename, PATHINFO_FILENAME);
            $type = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $size = Storage::size($filename);
            $old_directory = $filename;
            $new_directory = 'proofs/'.$user->username.'/evidence_'.$evidence->id.'/'.$name.'.'.$type;

            try {
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
                    'file_id' => $file_entity->id,
                ]);
            } catch (\Exception $e) {
                report($e);

                throw new ProofNotStored('No se pudo guardar la prueba '.$name, previous: $e);
            }

        }

    }

    // carpeta de las pruebas subidas en esta sesión
    private function temporary_folder($request): string
    {
        return 'tmp/'.Auth::user()->username.'/'.$request->session()->token().'/';
    }

    private function proof_not_stored()
    {
        return back()->withInput()->with('error', 'No se pudo guardar una de las pruebas. Vuelve a intentarlo.');
    }

    private function copy_files_into_temporary_folder($evidence)
    {

        $user = Auth::user();

        $token = session()->token();

        $proofs_folder = 'proofs/'.$user->username.'/evidence_'.$evidence->id;
        $tmp = 'tmp/'.$user->username.'/'.$token;

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

    public function edit($id)
    {

        $user = Auth::user();

        $token = session()->token();

        $evidence = Evidence::find($id);
        $committees = Committee::all();

        $tmp = 'tmp/'.$user->username.'/'.$token.'/';

        Storage::deleteDirectory($tmp);

        // generamos un nuevo token
        session()->regenerate();

        // copiamos las pruebas a una carpeta temporal para poder trabajar con los mismos
        $this->copy_files_into_temporary_folder($evidence);

        return view('evidence.createandedit', ['evidence' => $evidence,
            'committees' => $committees,
            'edit' => true,
            'route_draft' => route('evidence.draft.edit'),
            'route_publish' => route('evidence.publish.edit')]);
    }

    public function draft_edit(Request $request)
    {
        return $this->save($request, 'DRAFT');
    }

    public function publish_edit(Request $request)
    {
        return $this->save($request, $this->published_status());
    }

    private function save($request, $status)
    {

        // evidencia desde la que hemos decidido partir
        $evidence_previous = Evidence::find($request->_id);

        try {
            DB::transaction(function () use ($request, $status, $evidence_previous) {
                // creamos la nueva evidencia a partir de la seleccionada para editar
                $evidence_new = $this->new_evidence($request, $status);

                // evidencia cabecera en el flujo de ediciones (la última)
                $evidence_header = $evidence_previous->findHeaderEvidence();
                $evidence_header->last = false;
                $evidence_header->save();

                // apuntamos al final del flujo de ediciones
                $evidence_new->points_to = $evidence_header->id;
                $evidence_new->save();

                // copiamos ahora los archivos de la carpeta temporal a la nueva evidencia
                $this->save_files($request, $evidence_new);
            });
        } catch (ProofNotStored) {
            return $this->proof_not_stored();
        }

        // ya no necesitamos la carpeta temporal, la eliminamos
        Storage::deleteDirectory($this->temporary_folder($request));

        if ($status == 'DRAFT') {
            return redirect()->route('evidence.list')->with('success', 'Evidencia editada con éxito.');
        } else {
            return redirect()->route('evidence.list')->with('success', 'Evidencia publicada con éxito.');
        }

    }

    /****************************************************************************
     * REMOVE AN EVIDENCE
     ****************************************************************************/

    public function remove(Request $request)
    {
        $id = $request->_id;
        $evidence = Evidence::find($id);

        // eliminamos recursivamente la evidencia y todas las versiones anteriores, y después sus archivos
        $folders = DB::transaction(fn () => $this->delete_evidence($evidence));
        foreach ($folders as $folder) {
            Storage::deleteDirectory($folder);
        }

        return redirect()->route('evidence.list')->with('success', 'Evidencia borrada con éxito.');
    }

    // borra las filas de la versión y de las anteriores; devuelve las carpetas que se borran tras el commit
    private function delete_evidence($evidence): array
    {

        $user = Auth::user();

        // por si la evidencia apunta a otra anterior
        $evidence_previous = Evidence::find($evidence->points_to);

        // eliminamos los archivos almacenados
        $this->delete_files($evidence);
        $folders = ['proofs/'.$user->username.'/evidence_'.$evidence->id];
        $evidence->delete();

        if ($evidence_previous != null) {
            $folders = [...$folders, ...$this->delete_evidence($evidence_previous)];
        }

        return $folders;
    }

    private function delete_files($evidence)
    {
        foreach ($evidence->proofs as $proof) {
            $proof->file->delete();
        }
    }

    /****************************************************************************
     * REEDIT AN EVIDENCE
     ****************************************************************************/

    public function reedit(Request $request)
    {
        $evidence = Evidence::findOrFail($request->_id);

        // solo una evidencia rechazada (y que siga siendo la última versión) vuelve a borrador
        if ($evidence->status != 'REJECTED' || ! $evidence->last) {
            abort(404);
        }

        $evidence->status = 'DRAFT';

        $evidence->save();

        return redirect()->route('evidence.list')->with('success', 'Evidencia reasignada como borrador con éxito.');
    }
}
