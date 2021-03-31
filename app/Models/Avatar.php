<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{

    protected $fillable = ["user_id",'file_id'];
    protected $table = "avatar";

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function file()
    {
        return $this->belongsTo('App\Models\File');
    }
}
