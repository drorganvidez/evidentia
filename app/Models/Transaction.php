<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table="transactions";

    protected $fillable = [
        'id', 'reason', 'type', 'amount'
    ];

    /*
    * Obtener todas las transacciones
    */
    public function get_all_transactions(): \Illuminate\Support\Collection
    {
        $collection = collect();

        foreach($transactions as $transaction){
            $collection->push($transaction);
        }

        return $collection;
    }
}