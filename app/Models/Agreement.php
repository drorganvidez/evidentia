<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agreement extends Model
{
    protected $table = "agreements";

    public function point()
    {
        return $this->belongsTo('App\Models\Point');
    }
}
