<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proof extends Model
{

    protected $fillable = [
        'evidence_id', 'file_id'
    ];

    public function evidence()
    {
        return $this->belongsTo('App\Evidence');
    }

    public function file()
    {
        return $this->hasOne('App\File');
    }
}
