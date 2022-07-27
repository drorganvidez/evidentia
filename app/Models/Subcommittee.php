<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcommittee extends Model
{

    protected $table = 'subcommittees';

    public function committee()
    {
        return $this->belongsTo('App\Models\Committee');
    }
}
