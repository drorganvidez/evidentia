<?php

namespace App\Models;

use Config;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'surname', 'name', 'username', 'password', 'email', 'block', 'biography', 'clean_name', 'clean_surname',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function evidences()
    {
        return $this->hasMany(Evidence::class);
    }

    public function hasRole($rol_param): bool
    {
        return $this->roles->contains(fn ($role) => $role->rol === $rol_param);
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

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    public function signatureSheets()
    {
        return $this->belongsToMany(SignatureSheet::class)->withTimestamps()->orderByDesc('created_at');
    }

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function authoredThreads(): HasMany
    {
        return $this->hasMany(MessageThread::class, 'author_id');
    }

    public function evidence_rand()
    {
        return $this->evidences->where('rand', '1')->first();
    }

    public function evidence_rand_route()
    {
        try {
            return route('profiles.view.evidence', [
                'id_user' => $this->id,
                'id_evidence' => $this->evidence_rand()->id,
            ]);
        } catch (\Exception $e) {
            return '';
        }
    }

    public function evidence_rand_hours()
    {
        return optional($this->evidence_rand())->hours ?? 0;
    }

    // solo cuenta la última versión de cada evidencia; las anteriores son su historial
    private function latestEvidences()
    {
        return $this->evidences->where('last', true);
    }

    public function evidencesDraft()
    {
        return $this->latestEvidences()->where('status', 'DRAFT');
    }

    public function evidencesNotDraft()
    {
        return $this->latestEvidences()->where('status', '!=', 'DRAFT');
    }

    public function evidencesPending()
    {
        return $this->latestEvidences()->where('status', 'PENDING');
    }

    public function evidencesAccepted()
    {
        return $this->latestEvidences()->where('status', 'ACCEPTED');
    }

    public function evidencesRejected()
    {
        return $this->latestEvidences()->where('status', 'REJECTED');
    }

    public function attendees_pending()
    {
        return $this->attendees->where('status', 'Attending');
    }

    public function attendees_checkedin()
    {
        return $this->attendees->where('status', 'Checked In');
    }

    private function collection_hours($collection)
    {
        return $collection->pluck('hours')->sum();
    }

    private function collection_count($collection)
    {
        return $collection->count();
    }

    public function evidences_accepted_hours()
    {
        return $this->collection_hours($this->evidencesAccepted());
    }

    public function evidences_accepted_count()
    {
        return $this->collection_count($this->evidencesAccepted());
    }

    public function meetings_hours()
    {
        return $this->collection_hours($this->meetings);
    }

    public function meetings_count()
    {
        return $this->collection_count($this->meetings);
    }

    public function attendees_checkedin_count()
    {
        return $this->collection_count($this->attendees_checkedin());
    }

    public function attendees_hours()
    {
        if ($this->attendees_checkedin_count() === 0) {
            return 0;
        }

        return $this->attendees_checkedin()->sum(fn ($a) => $a->event->hours);
    }

    // horas de asistencia que cuentan, con el tope del curso si lo hay
    public function events_hours()
    {
        $limit = Config::max_attendees_hours();

        return $limit === null ? $this->attendees_hours() : min($this->attendees_hours(), $limit);
    }

    public function max_events_hours()
    {
        return $this->events_hours();
    }

    // horas de evidencias aceptadas que cuentan, con el tope del curso si lo hay
    public function evidences_computed_hours()
    {
        $limit = Config::max_evidences_hours();

        return $limit === null ? $this->evidences_accepted_hours() : min($this->evidences_accepted_hours(), $limit);
    }

    public function events_count()
    {
        return $this->attendees_checkedin_count();
    }

    public function bonus_hours()
    {
        return $this->collection_hours($this->bonus);
    }

    public function total_computed_hours()
    {
        return $this->evidences_computed_hours() + $this->meetings_hours() + $this->events_hours() + $this->bonus_hours();
    }

    public function associate_committee()
    {
        if ($this->hasRole('COORDINATOR')) {
            return $this->coordinator->committee->name ?? 'None';
        } elseif ($this->hasRole('SECRETARY')) {
            return $this->secretary->committee->name ?? 'None';
        }

        return 'None';
    }

    public function committee_belonging()
    {
        $names = $this->evidences
            ->filter(fn ($e) => $e->status === 'ACCEPTED' && $e->committee)
            ->map(fn ($e) => $e->committee->name)
            ->unique()
            ->filter();

        if ($this->hasRole('COORDINATOR')) {
            $names->push($this->coordinator->committee->name);
        } elseif ($this->hasRole('SECRETARY')) {
            $names->push($this->secretary->committee->name);
        }

        return $names->implode(' | ');
    }
}
