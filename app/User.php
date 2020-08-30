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
        'dni','surname','name', 'username','password','email','block','biography'
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

    public function attendees()
    {
        return $this->hasMany('App\Attendee');
    }

    public function evidence_rand(){
        return $this->evidences->where('rand','=', '1')->first();
    }

    public function evidence_rand_route(){

        $route = "";
        try{
            $route = route('profiles.view.evidence',['instance' => \Instantiation::instance(), 'id_user' => $this->id, 'id_evidence' => $this->evidence_rand()->id]);
        }catch (\Exception $e){
            $route = "";
        }

        return $route;
    }

    public function evidence_rand_hours(){
        try{
            return $this->evidence_rand()->hours;
        }catch (\Exception $e){
            return 0;
        }
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

    // Asistencias pendientes
    public function attendees_pending()
    {
        return $this->attendees->where('status','=', 'Attending');
    }

    // Asistencias confirmadas
    public function attendees_checkedin()
    {
        return $this->attendees->where('status','=', 'Checked In');
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

    // Asistencias totales pendientes
    public function attendees_pending_count()
    {
        return $this->collection_count($this->attendees_pending());
    }

    // Asistencias totales confirmadas
    public function attendees_checkedin_count()
    {
        return $this->collection_count($this->attendees_checkedin());
    }

    // Horas de asistencia
    public function attendees_hours()
    {
        $hours =  $this->attendees_checkedin()->map(function ($item, $key) {
            return $item->event->hours;
        });
        return $hours->sum();
    }

    public function events_hours()
    {
        return $this->attendees_hours();
    }

    public function events_count()
    {
        return $this->attendees_checkedin_count();
    }

    // Bonos
    public function bonus_hours()
    {
        return $this->collection_hours($this->bonus);
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

    public function committee_belonging()
    {

        // se obtienen los comités de las evidencias validadas
        $comittees_names =  $this->evidences->map(function ($item, $key) {
            if($item->status == 'ACCEPTED') {
                return $item->comittee->name;
            }
        });

        // se eliminan comités repetidos
        $comittees_names = $comittees_names->unique();

        // eliminamos los comités nulos
        $comittees_names =  $comittees_names ->filter(function ($value, $key) {
            return $value != null;
        });

        $comittee = "";

        // por defecto, si el alumno es coordinador o secretario, se añade el comité asociado
        if($this->hasRole('COORDINATOR')){
            $comittee = $this->coordinator->comittee->name;
        }elseif ($this->hasRole('SECRETARY')){
            $comittee = $this->secretary->comittee->name;
        }

        $comittees_names->push($comittee);

        $comittees_names = $comittees_names->implode(" | ");

        return $comittees_names;
    }
}
