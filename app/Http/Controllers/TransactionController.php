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

   //     $request->validate([
   //         'reason' => 'required|min:10|max:40',
     //       'type' => 'required',
       //     'amount' => 'required'
       // ]); 

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
}