<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secretary extends Model
{
    protected $table = "secretaries";

    public function comittee()
    {
        return $this->belongsTo('App\Models\Comittee');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function default_lists()
    {
        return $this->hasMany('App\Models\DefaultList')->orderByDesc('created_at');
    }
}
