<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{

    protected $table = "bonus";

    protected $fillable = ["reason", "hours"];

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function comittee()
    {
        return $this->belongsTo('App\Models\Comittee');
    }
}
