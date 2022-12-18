<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kanban extends Model
{
    protected $table="kanban";

    protected $fillable = [
        'id', 'title', 'user_id', 'comittee_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function comittee()
    {
        return $this->belongsTo('App\Models\Comittee');
    }

    public function issue(){
        return $this->belongsTo('App\Models\Issue');
    }

}

