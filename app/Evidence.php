<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{

    protected $table="evidences";

    protected $fillable = [
        'title', 'description', 'hours', 'user_id', 'comittee_id', 'points_to', 'status', 'stamp'
    ];

    public function proofs()
    {
        return $this->hasMany('App\Proof');
    }
}
