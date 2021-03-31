<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaryPoints extends Model
{
    public function Diary()
    {
        return $this->belongsTo('App\Models\Diary');
    }
}
