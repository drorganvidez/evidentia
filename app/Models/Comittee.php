<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comittee extends Model
{
    protected $table = 'comittees';

    protected $fillable = ["icon","name"];

    public function subcomittees()
    {
        return $this->hasMany('App\Models\Subcomittee');
    }

    public function evidences()
    {
        return $this->hasMany('App\Models\Evidence');
    }

    public function evidences_not_draft() {
        return $this->evidences()->where('status','!=', 'DRAFT')->orderByDesc('updated_at');
    }

    public function evidences_draft() {
        return $this->evidences()->where('status','=', 'DRAFT')->orderByDesc('updated_at');
    }

    public function evidences_pending() {
        return $this->evidences()->where('status','=', 'PENDING')->orderByDesc('updated_at');
    }

    public function evidences_accepted() {
        return $this->evidences()->where('status','=', 'ACCEPTED')->orderByDesc('updated_at');
    }

    public function evidences_rejected() {
        return $this->evidences()->where('status','=', 'REJECTED')->orderByDesc('updated_at');
    }

    public function coordinators()
    {
        return $this->hasMany('App\Models\Coordinator');
    }

    public function secretaries()
    {
        return $this->hasMany('App\Models\Secretary');
    }

    public function meetings()
    {
        return $this->hasMany('App\Models\Meeting')->orderByDesc('datetime');
    }

    public function meetings_requests()
    {
        return $this->hasMany('App\Models\MeetingRequest')->orderByDesc('datetime');
    }

    public function bonus()
    {
        return $this->hasMany('App\Models\Bonus')->orderByDesc('created_at');
    }

    public function can_be_removed()
    {
        /**
         * Un comité puede ser eliminado si se cumplen las tres condiciones siguientes:
         *
         * 1)   No tiene ningún coordinador ni secretario
         * 2)   No tiene evidencias asociadas, sea cual sea su estado
         * 3)   No hay reuniones asociadas
         */

        // 1)
        if($this->coordinators->count() > 0 || $this->secretaries->count() > 0){
            return false;
        }

        // 2)
        if($this->evidences->count() > 0){
            return false;
        }

        // 3)
        if($this->meetings->count() > 0){
            return false;
        }
        return true;

    }

    public function get_all_meeting_requests(): \Illuminate\Support\Collection
    {
        $collection = collect();

        foreach($this->secretaries as $secretary){
            $meeting_requests = $secretary->meeting_requests;
            foreach($meeting_requests as $meeting_request){
                $collection->push($meeting_request);
            }
        }

        return $collection;
    }

    public function get_all_meeting_minutes(): \Illuminate\Support\Collection
    {
        $collection = collect();

        foreach($this->secretaries as $secretary){
            $meeting_minutes = $secretary->meeting_minutes;
            foreach($meeting_minutes as $mm){
                $collection->push($mm);
            }
        }

        return $collection;
    }
}
