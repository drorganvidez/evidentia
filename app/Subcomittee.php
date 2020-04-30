<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subcomittee extends Model
{
    public function comittee()
    {
        return $this->belongsTo('App\Comittee');
    }
}
