<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidenceProof extends Model
{

    protected $fillable = [
        'incidence_id', 'file_id'
    ];

    public function incidence()
    {
        return $this->belongsTo('App\Models\Incidence');
    }

    public function file()
    {
        return $this->belongsTo('App\Models\File');
    }

    public function integrity()
    {
        return $this->file->stamp == \Stamp::get_stamp_file($this->file);
    }
}
