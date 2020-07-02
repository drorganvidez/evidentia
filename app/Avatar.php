<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{

    protected $fillable = ["user_id",'file_id'];
    protected $table = "avatar";

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function file()
    {
        return $this->belongsTo('App\File');
    }
}
