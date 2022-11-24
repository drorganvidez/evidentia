<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collaborator extends Model
{

    protected $table = "collaborators";

    public function comittee()
    {
        return $this->belongsTo('App\Models\Comittee','comittee_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
