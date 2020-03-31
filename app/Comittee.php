<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comittee extends Model
{
    protected $table = 'comittees';

    public function evidences()
    {
        return $this->hasMany('App\Evidence');
    }
}
