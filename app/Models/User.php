<?php

namespace App\Models;

use Config;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Models\Role;
use App\Models\Evidence;
use App\Models\Coordinator;
use App\Models\Secretary;
use App\Models\Meeting;
use App\Models\Bonus;
use App\Models\Avatar;
use App\Models\Attendee;
use App\Models\SignatureSheet;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'surname', 'name', 'username', 'password', 'email', 'block', 'biography', 'clean_name', 'clean_surname'
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
        return $this->roles->contains(fn($role) => $role->rol === $rol_param);
    }

    public function isAdmin(): bool
    {
        return (bool)($this->administrator ?? false);
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

    public function signatureSheets()
    {
        return $this->belongsToMany(SignatureSheet::class)->withTimestamps()->orderByDesc('created_at');
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
            return "";
        }
    }

    public function evidence_rand_hours()
    {
        return optional($this->evidence_rand())->hours ?? 0;
    }

    public function evidences_draft()
    {
        return $this->evidences->where('status', 'DRAFT');
    }

    public function evidencesNotDraft()
    {
        return $this->evidences->where('status', '!=', 'DRAFT');
    }

    public function evidences_pending()
    {
        return $this->evidences->where('status', 'PENDING');
    }

    public function evidences_accepted()
    {
        return $this->evidences->where('status', 'ACCEPTED');
    }

    public function evidences_rejected()
    {
        return $this->evidences->where('status', 'REJECTED');
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

    public function evidences_hours()
    {
        return $this->collection_hours($this->evidences);
    }

    public function evidences_count()
    {
        return $this->collection_count($this->evidences);
    }

    public function evidences_draft_hours()
    {
        return $this->collection_hours($this->evidences_draft());
    }

    public function evidences_draft_count()
    {
        return $this->collection_count($this->evidences_draft());
    }

    public function evidences_pending_hours()
    {
        return $this->collection_hours($this->evidences_pending());
    }

    public function evidences_pending_count()
    {
        return $this->collection_count($this->evidences_pending());
    }

    public function evidences_accepted_hours()
    {
        return $this->collection_hours($this->evidences_accepted());
    }

    public function evidences_accepted_count()
    {
        return $this->collection_count($this->evidences_accepted());
    }

    public function evidences_rejected_hours()
    {
        return $this->collection_hours($this->evidences_rejected());
    }

    public function evidences_rejected_count()
    {
        return $this->collection_count($this->evidences_rejected());
    }

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
        return Meeting::count();
    }

    public function attendees_pending_count()
    {
        return $this->collection_count($this->attendees_pending());
    }

    public function attendees_checkedin_count()
    {
        return $this->collection_count($this->attendees_checkedin());
    }

    public function attendees_hours()
    {
        if ($this->attendees_checkedin_count() === 0) return 0;
        return $this->attendees_checkedin()->sum(fn($a) => $a->event->hours);
    }

    public function events_hours()
    {
        return $this->attendees_hours();
    }

    public function max_events_hours()
    {
        return min($this->attendees_hours(), Config::max_attendees_hours());
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
        return $this->evidences_accepted_hours() + $this->meetings_hours() + $this->events_hours() + $this->bonus_hours();
    }

    public function avatar_route()
    {
        return $this->avatar
            ? URL::to('/uploads/avatars/' . $this->avatar->file->name . '.' . $this->avatar->file->type)
            : URL::to('/uploads/avatars/default.png');
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
            ->filter(fn($e) => $e->status === 'ACCEPTED' && $e->committee)
            ->map(fn($e) => $e->committee->name)
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
