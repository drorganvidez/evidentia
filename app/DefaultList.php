<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DefaultList extends Model
{

    protected $table = "defaultlist";

    protected $fillable = ['id','name'];

    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    public function defaultlist()
    {
        return $this->belongsTo('App\Comittee');
    }
}
