<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{

    protected $table="evidences";

    protected $fillable = [
        'id', 'title', 'description', 'hours', 'user_id', 'committee_id', 'points_to', 'status', 'stamp', 'rand', 'temp'
    ];

    public function proofs()
    {
        return $this->hasMany('App\Models\Proof');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function committee()
    {
        return $this->belongsTo('App\Models\Committee');
    }

    public function reason_rejection()
    {
        return $this->hasOne('App\Models\ReasonRejection');
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

    // obtiene la evidencia que va a la cabeza del flujo de ediciones
    public function find_header_evidence()
    {
        return $this->find_header_evidence_p($this);
    }

    private function find_header_evidence_p($evidence)
    {
        if($evidence->last)
            return $evidence;
        else {
            $points_me = Evidence::where('points_to',$evidence->id)->first();
            return $this->find_header_evidence_p($points_me);
        }
    }

    public static function evidences_not_draft() {
        return Evidence::where('status','!=', 'DRAFT')->orderByDesc('updated_at')->get();
    }

    public static function evidences_draft() {
        return Evidence::where('status','=', 'DRAFT')->where('points_to','=',null)->orderByDesc('updated_at')->get();
    }

    public static function evidences_pending() {
        return Evidence::where('status','=', 'PENDING')->orderByDesc('updated_at')->get();
    }

    public static function evidences_accepted() {
        return Evidence::where('status','=', 'ACCEPTED')->orderByDesc('updated_at')->get();
    }

    public static function evidences_rejected() {
        return Evidence::where('status','=', 'REJECTED')->orderByDesc('updated_at')->get();
    }

    public function integrity()
    {
        return $this->stamp == \Stamp::get_stamp_evidence($this);
    }

    public function files()
    {
        $collect = collect();
        foreach($this->proofs as $proof){
            $collect->push($proof->file);
        }
        return $collect;
    }

}
