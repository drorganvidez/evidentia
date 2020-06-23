<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coordinator extends Model
{

    protected $table = "coordinators";

    public function comittee()
    {
        return $this->belongsTo('App\Comittee','comittee_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
