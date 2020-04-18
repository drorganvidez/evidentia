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

    /**
     * @return mixed
     * Evidence Flow
     */

    // lista las evidencias ANTERIORES a esta
    public function previous_evidences()
    {
        $evidence_previous = Evidence::find($this->points_to);

        // si es null, es que el flujo solo contiene una evidencia
        if($evidence_previous == null){
            $evidences = collect();
            $evidences->push($this);
            return $evidences;
        }else{
            return $this->previous_evidences_p($evidence_previous,collect());
        }

    }

    private function previous_evidences_p($evidence,$collection)
    {

        $collection->push($evidence);

        if($evidence->points_to == null){
            return $collection;
        }else{
            $evidence_previous = Evidence::find($evidence->points_to);
            return $this->previous_evidences_p($evidence_previous,$collection);
        }
    }

    // lista las evidendes POSTERIORES a esta
    public function later_evidences()
    {
        $points_me = Evidence::where('points_to',$this->id)->first();

        // si es null, es que el flujo solo contiene una evidencia
        if($points_me == null){
            $evidences = collect();
            $evidences->push($this);
            return $evidences;
        }else{
            return $this->later_evidences_p($points_me,collect());
        }

    }

    private function later_evidences_p($evidence,$collection)
    {
        $collection->push($evidence);

        if($evidence->last)
            return $collection;
        else {
            $points_me = Evidence::where('points_to',$evidence->id)->first();
            return $this->later_evidences_p($points_me,$collection);
        }
    }

    // lista el flujo total de ediciones de evidencias, desde la primera edición a la última
    public function flow_evidences(){
        $previous_evidences = $this->previous_evidences();
        $later_evidences = $this->later_evidences();
        return $previous_evidences->concat($later_evidences)->push($this)->unique()->sortByDesc('created_at');
    }

}
