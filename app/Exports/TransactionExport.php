<?php

namespace App\Exports;

use App\Models\Comittee;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Transaction;
use App\Models\User;

class TransactionExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public $type = null;

    public function __construct($type){
        $this->type = $type;
    }
    public function collection()
    {
        $res = collect();
        if (Auth::User()->hasRole('PRESIDENT') and $this->type == 'all') {
        $transactions = Transaction::all();
        }
        if(Auth::User()->hasRole('COORDINATOR') and $this->type == 'mine'){
            $transactions = Transaction::where(['user_id' => Auth::id()])->get();
        }
        foreach ($transactions as $transaction) {

                $userId = $transaction->user_id;
                $comiteeId = $transaction->comittee_id;
                $user = User::find($userId);
                $comitee = Comittee::find($comiteeId);
                $array = [
                    'Concepto' => strtoupper(trim($transaction->reason)),
                    'Estado' => strtoupper(trim($transaction->status)),
                    'Tipo' => strtoupper(trim($transaction->type)),
                    'Cantidad' => strtoupper(trim($transaction->amount)),
                    'Usuario' => strtoupper(trim($user->username)),
                    'Comite' => strtoupper(trim($comitee->name)),
                    'Fecha' =>  strtoupper(trim($transaction->created_at))             
                ];

                $object = (object)$array;
                $res->push($object);
            }
        return $res;
    }

    public function headings(): array
    {
        return [
            'Concepto',
            'Estado',
            'Tipo',
            'Cantidad',
            'Usuario',
            'Comite',
            'Fecha'
        ];
    }

}
