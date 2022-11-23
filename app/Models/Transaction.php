<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table="transactions";

    protected $fillable = [
        'id', 'reason', 'type', 'status', 'amount', 'comittee_id', 'user_id'
    ];

    public function comittee()
    {
        return $this->belongsTo('App\Models\Comittee');
    }

    /*
    * Obtener todas las transacciones
    */
    public static function get_all_transactions(): \Illuminate\Support\Collection
    {
        $collection = collect();

        foreach($transactions as $transaction){
            $collection->push($transaction);
        }

        return $collection;
    }
}