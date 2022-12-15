<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifiedProof extends Model
{
    protected $table = "verified_proofs";

    protected $fillable = [
        'evidence_id','file_id','name', 'type', 'size', 'lecturer_id'
    ];

    public function evidence()
    {
        return $this->belongsTo('App\Models\Evidence');
    }

    public function lecturer()
    {
        return $this->belongsTo('App\Models\User');
    }

}
