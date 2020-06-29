<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Secretary extends Model
{
    public function comittee()
    {
        return $this->belongsTo('App\Comittee');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function default_lists()
    {
        return $this->hasMany('App\DefaultList');
    }
}
