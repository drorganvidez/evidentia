<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incidence extends Model {
    
    protected $table = "incidence";

    protected $fillable = [
      'id','title','datetime','description', 'status','close_reason', 'stamp','user_id', 'comittee_id',
    ];
    public function proofs()
    {
        return $this->hasMany('App\Models\IncidenceProof');
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }


    public function comittee()
    {
        return $this->belongsTo('App\Models\Comittee');
    }


}
