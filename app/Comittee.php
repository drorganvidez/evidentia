<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comittee extends Model
{
    protected $table = 'comittees';

    protected $fillable = ["icon","name"];

    public function subcomittees()
    {
        return $this->hasMany('App\Subcomittee');
    }

    public function evidences()
    {
        return $this->hasMany('App\Evidence');
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
        return $this->hasMany('App\Coordinator');
    }

    public function secretaries()
    {
        return $this->hasMany('App\Secretary');
    }

    public function meetings()
    {
        return $this->hasMany('App\Meeting')->orderByDesc('datetime');
    }

    public function bonus()
    {
        return $this->hasMany('App\Bonus')->orderByDesc('created_at');
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
}
