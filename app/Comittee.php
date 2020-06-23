<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comittee extends Model
{
    protected $table = 'comittees';

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

    public function defaultList()
    {
        return $this->hasMany('App\DefaultList');
    }

    public function meetings()
    {
        return $this->hasMany('App\Meeting');
    }
}
