<?php

namespace App\Http\Controllers;

use App\Exports\MyEvidencesExport;
use App\Models\Comittee;
//use App\Models\Evidence;
//use App\Models\File;
//use App\Models\Proof;
use App\Models\Transaction;
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
use App\Exports\TransactionExport;
use App\Http\Services;

class TransactionController extends Controller
{   
    // List: lista todas las transacciones de un coordinador
    public function list()
    {
        $transactions = Transaction::where(['user_id' => Auth::id()])->get();
        $instance = \Instantiation::instance();

        $transactions = $transactions->reverse();

        return view('transaction.list',
            ['instance' => $instance, 
            'transactions' => $transactions]);
    }


    // CREAR TRANSACCION

    // Esta muestra la vista para crear una transacción
    public function create()
    {
        $instance = \Instantiation::instance();
        $comittees = Comittee::all();

        return view('transaction.createandedit', ['route_publish' => route('transaction.publish',$instance),
                                            'instance' => $instance,
                                            'comittees' => $comittees]);
    }


    // Publish: es el controlador cuando hacemos click en el boton de crear transaccion
    public function publish(Request $request)
    {
        return $this->new($request,"PENDING");
    }

    

    // Guarda la transacción en base de datos
    private function new($request,$status)
    {

        $instance = \Instantiation::instance();

        $transaction = $this->new_transaction($request,$status);

        return redirect()->route('transaction.list',$instance)->with('success', 'Transacción creada con éxito.');

    }

    // Crea una nueva instancia de transaccion
    private function new_transaction($request,$status)
    {

        $request->validate([
            'reason' => 'required|min:10|max:40',
            'type' => 'required',
            'amount' => 'required'
       ]); 

        // datos necesarios para crear evidencias
        $user = Auth::user();

        // creación de una nueva evidencia
        $transaction = Transaction::create([
            'reason' => $request->input('reason'),
            'status' => $status,
            'type' => $request->input('type'),
            'amount' => $request->input('amount'),
            'user_id' => $user->id,
            'comittee_id' => $request->input('comittee')
        ]);

        // cómputo del sello
       // $transaction = \Stamp::compute_transaction($transaction);
        $transaction->save();

        return $transaction;
    }

 

    
    // LISTAR TODAS LAS TRANSACCIONES POR EL COORDIANDOR
    public function all()
    {
        $transactions = Transaction::all();
        $instance = \Instantiation::instance();
        $transactions = $transactions->reverse();

        return view('transaction.coordinator.list',
            ['instance' => $instance, 'transactions' => $transactions]);

    }



    // RECHAZAR TRANSACCION

    public function rejected($instance, $id)
    {
        $instance = \Instantiation::instance();

        $transaction = Transaction::find($id);
        $transaction->status = 'REJECTED';
        $transaction->save();

        return redirect()->route('transaction.list.all', $instance)->with('success', 'Transacción rechazada con éxito .');
    }




    // ACEPTAR TRANSACCION

    public function accepted($instance, $id)
    {
        $instance = \Instantiation::instance();

        $transaction = Transaction::where();
        $transaction->status = 'ACCEPTED';
        $transaction->save();

        return redirect()->route('transaction.list.all', $instance)->with('success', 'Transacción aceptada con éxito.');
    }




    // EXPORTAR TRANSACCIONES ( CSV, PDF, XLSX)


    public function transaction_export($instance,$type, $ext)
    {
        try {
            ob_end_clean();
            if (!in_array($ext, ['csv', 'pdf', 'xlsx'])) {
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            if(!in_array($type, ['all', 'mine'])) {
                return back()->with('error', 'Mal formato de type');
            }
            return Excel::download(new TransactionExport($type), 'transacciones-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

}