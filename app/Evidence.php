<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{

    protected $table="evidences";

    protected $fillable = [
        'id', 'title', 'description', 'hours', 'user_id', 'comittee_id', 'points_to', 'status', 'stamp'
    ];

    public function proofs()
    {
        return $this->hasMany('App\Proof');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function comittee()
    {
        return $this->belongsTo('App\Comittee');
    }

}
