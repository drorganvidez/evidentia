<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{

    protected $table = "bonus";

    protected $fillable = ["reason", "hours"];

    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    public function comittee()
    {
        return $this->belongsTo('App\Comittee');
    }
}
