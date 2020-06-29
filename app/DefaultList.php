<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DefaultList extends Model
{

    protected $table = "defaultlist";

    protected $fillable = ['id','name','secretary_id'];

    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    public function secretary()
    {
        return $this->belongsTo('App\Secretary');
    }
}
