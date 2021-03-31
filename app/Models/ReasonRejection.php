<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReasonRejection extends Model
{
    protected $table="reason_rejection";

    protected $fillable = [
        'id', 'reason', 'evidence_id'
    ];

    public function evidence()
    {
        return $this->belongsTo('App\Models\Evidence');
    }
}
