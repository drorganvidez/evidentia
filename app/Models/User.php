<?php

namespace App\Models;

use App\Http\Services\EvidenceService;
use Config;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'surname',
        'name',
        'username',
        'password',
        'email',
        'block',
        'biography',
        'clean_name',
        'clean_surname'
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
        return $this->belongsToMany(Role::class);
    }

    public function evidences()
    {
        return $this->hasMany(Evidence::class);
    }

    public function guest_evidences()
    {
        return $this->hasMany(Evidence::class, 'guest_id', 'user_id');
    }

    public function hasRole($rol_param)
    {
        try {
            foreach ($this->roles as $rol) {
                if ($rol->rol == $rol_param) {
                    return true;
                }
            }
        }catch(\Exception $e){

        }
        return false;
    }

    public function isAdmin()
    {
        try {
            return $this->administrator;
        }catch(\Exception $e){

        }

        return false;
    }

    public function full_name()
    {
        return $this->surname . ', ' . $this->name;
    }

    public function coordinator()
    {
        return $this->hasOne(Coordinator::class);
    }

    public function secretary()
    {
        return $this->hasOne(Secretary::class);
    }

    public function meetings()
    {
        return $this->belongsToMany(Meeting::class);
    }

    public function bonus()
    {
        return $this->belongsToMany(Bonus::class);
    }

    public function avatar()
    {
        return $this->hasOne(Avatar::class);
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    public function signature_sheets()
    {
        return $this->belongsToMany(SignatureSheet::class)->withTimestamps()->orderByDesc('created_at');
    }

    public function api_tokens()
    {
        return $this->hasMany(ApiToken::class);
    }

    public function evidences_draft_count(): int
    {
        $service = new EvidenceService();
        return $service->count_evidences_in_draft_by_user($this);
;    }

    public function evidences_pending_count(): int
    {
        $service = new EvidenceService();
        return $service->count_evidences_pending_by_user($this);
    }

    public function evidences_accepted_count(): int
    {
        $service = new EvidenceService();
        return $service->count_evidences_accepted_by_user($this);
    }

    public function evidences_rejected_count(): int
    {
        $service = new EvidenceService();
        return $service->count_evidences_rejected_by_user($this);
    }

    public function total_evidences_hours(): float
    {
        $service = new EvidenceService();
        return $service->evidences_hours_by_user($this);
    }

    /*

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

    public function meetings_total_count()
    {
        return Meeting::all()->count();
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
        if($this->attendees_checkedin_count() == 0) return "0";

        $hours =  $this->attendees_checkedin()->map(function ($item, $key) {
            return $item->event->hours;
        });
        return $hours->sum();
    }

    public function events_hours()
    {
        return $this->attendees_hours();
    }

    public function max_events_hours()
    {

        if($this->attendees_hours() >= Config::max_attendees_hours()){
            return Config::max_attendees_hours();
        }

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

    // Total de horas computadas
    public function total_computed_hours(){
        return $this->evidences_accepted_hours() + $this->meetings_hours() + $this->events_hours() + $this->bonus_hours();
    }

    public function avatar_route()
    {

        if($this->avatar == null){
            return URL::to('/').'/uploads/avatars/default.png';
        }

        return URL::to('/').'/uploads/avatars/'.$this->avatar->file->name.'.'.$this->avatar->file->type;

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

    */
}
