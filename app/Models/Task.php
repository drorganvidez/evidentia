<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{

    protected $table="tasks";

    protected $fillable = [
        'id', 'title', 'description', 'hours', 'user_id', 'comittee_id', 'start_date', 'end_date'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function comittee()
    {
        return $this->belongsTo('App\Models\Comittee');
    }


}
