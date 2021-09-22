<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaryPoints extends Model
{

    protected $table = "diary_points";

    protected $fillable = [
        "diary_id",
        "point"
    ];

    public function Diary()
    {
        return $this->belongsTo('App\Models\Diary');
    }
}
