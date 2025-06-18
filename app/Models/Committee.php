<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Committee extends Model
{
    protected $table = 'committees';

    protected $fillable = ['icon', 'name'];

    // Relaciones

    public function evidences()
    {
        return $this->hasMany(Evidence::class);
    }

    public function coordinators()
    {
        return $this->hasMany(Coordinator::class);
    }

    public function secretaries()
    {
        return $this->hasMany(Secretary::class);
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class)->orderByDesc('datetime');
    }

    public function meetings_requests()
    {
        return $this->hasMany(MeetingRequest::class)->orderByDesc('datetime');
    }

    public function bonus()
    {
        return $this->hasMany(Bonus::class)->orderByDesc('created_at');
    }

    // Filtros de evidencias
    public function evidencesNotDraft()
    {
        return $this->evidences()->where('status', '!=', 'DRAFT')->orderByDesc('updated_at');
    }

    public function evidencesDraft()
    {
        return $this->evidences()->where('status', 'DRAFT')->orderByDesc('updated_at');
    }

    public function evidencesPending()
    {
        return $this->evidences()->where('status', 'PENDING')->orderByDesc('updated_at');
    }

    public function evidencesAccepted()
    {
        return $this->evidences()->where('status', 'ACCEPTED')->orderByDesc('updated_at');
    }

    public function evidencesRejected()
    {
        return $this->evidences()->where('status', 'REJECTED')->orderByDesc('updated_at');
    }

    // Lógica de eliminación
    public function can_be_removed(): bool
    {
        return
            $this->coordinators->isEmpty() &&
            $this->secretaries->isEmpty() &&
            $this->evidences->isEmpty() &&
            $this->meetings->isEmpty();
    }

    // Métodos auxiliares
    public function get_all_meeting_requests(): Collection
    {
        return $this->secretaries->flatMap(fn ($s) => $s->meeting_requests);
    }

    public function get_all_meeting_minutes(): Collection
    {
        return $this->secretaries->flatMap(fn ($s) => $s->meeting_minutes);
    }
}
