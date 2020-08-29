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
        return $this->belongsTo('App\File');
    }

    public function integrity()
    {
        return $this->file->stamp == \Stamp::get_stamp_file($this->file);
    }
}
