<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dni','surname','name', 'username','password','email','block',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany('App\Role');
    }

    public function evidences()
    {
        return $this->hasMany('App\Evidence');
    }

    public function hasRole($rol_param)
    {
        foreach($this->roles as $rol)
        {
            if($rol->rol == $rol_param)
            {
                return true;
            }
        }
        return false;
    }

    public function coordinator()
    {
        return $this->hasOne('App\Coordinator');
    }

    public function secretary()
    {
        return $this->hasOne('App\Secretary');
    }

    /*public function defaultLists()
    {
        return $this->hasMany('App\DefaultList');
    }*/

    public function meetings()
    {
        return $this->belongsToMany('App\Meeting');
    }

    public function bonus()
    {
        return $this->belongsToMany('App\Bonus');
    }

    public function avatar()
    {
        return $this->hasOne('App\Avatar');
    }

    public function evidences_draft() {
        return $this->evidences->where('status','=', 'DRAFT');
    }

    public function evidences_not_draft() {
        return $this->evidences->where('status','!=', 'DRAFT');
    }

    public function evidences_pending() {
        return $this->evidences->where('status','=', 'PENDING');
    }

    public function evidences_accepted() {
        return $this->evidences->where('status','=', 'ACCEPTED');
    }

    public function evidences_rejected() {
        return $this->evidences->where('status','=', 'REJECTED');
    }

    /*
     *  MÉTODOS DERIVADOS DE INTERÉS
     */

     private function collection_hours($collection)
     {
         $hours =  $collection->map(function ($item, $key) {
             return $item->hours;
         });
         return $hours->sum();
     }

    private function collection_count($collection)
    {
        return $collection->count();
    }

    // Todas las evidencias

    public function evidences_hours()
    {
        return $this->collection_hours($this->evidences);
    }

    public function evidences_count()
    {
        return $this->collection_count($this->evidences);
    }

    // Evidencias en borrador

    public function evidences_draft_hours()
    {
        return $this->collection_hours($this->evidences_draft());
    }

    public function evidences_draft_count()
    {
        return $this->collection_count($this->evidences_draft());
    }

    // Evidencias pendientes

    public function evidences_pending_hours()
    {
        return $this->collection_hours($this->evidences_pending());
    }

    public function evidences_pending_count()
    {
        return $this->collection_count($this->evidences_pending());
    }

    // Evidencias aceptadas

    public function evidences_accepted_hours()
    {
        return $this->collection_hours($this->evidences_accepted());
    }

    public function evidences_accepted_count()
    {
        return $this->collection_count($this->evidences_accepted());
    }

    // Evidencias rechazadas

    public function evidences_rejected_hours()
    {
        return $this->collection_hours($this->evidences_rejected());
    }

    public function evidences_rejected_count()
    {
        return $this->collection_count($this->evidences_rejected());
    }

    // Reuniones

    public function meetings_hours()
    {
        return $this->collection_hours($this->meetings);
    }

    public function meetings_count()
    {
        return $this->collection_count($this->meetings);
    }

    // Eventos
    public function events_hours()
    {
        return 0;
    }

    public function events_count()
    {
        return 0;
    }

    // Bonos
    public function bonus_hours()
    {
        return 0;
    }

    public function avatar_route()
    {
        $instance = \Instantiation::instance();
        return route('avatar',['instance' => $instance, 'id' => Auth::id()]);
    }

    public function associate_comittee()
    {
        // ¿es coordinador o secretario?
        if($this->hasRole('COORDINATOR')){
            return $this->coordinator->comittee->name;
        }elseif ($this->hasRole('SECRETARY')){
            return $this->secretary->comittee->name;
        }else{
            return "None";
        }
    }
}
