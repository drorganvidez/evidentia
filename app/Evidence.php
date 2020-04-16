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

    public function previous_evidences()
    {
        $evidence_previous = Evidence::find($this->points_to);
        return $this->previous_evidences_p($evidence_previous,collect());
    }

    private function previous_evidences_p($evidence,$collection)
    {
        if($evidence->points_to == null){
            return $collection;
        }else{
            $collection->push($evidence);
            $evidence_previous = Evidence::find($evidence->points_to);
            return $this->previous_evidences_p($evidence_previous,$collection);
        }
    }

}
