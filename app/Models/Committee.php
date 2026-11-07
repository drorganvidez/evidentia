<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Committee extends Model
{
    protected $table = 'committees';

    protected $fillable = ['icon', 'name'];

    protected function casts(): array
    {
        return ['presidency' => 'boolean'];
    }

    /** The committee of the presidency (App\Services\ReviewQueue), the first one marked if there were more. */
    public static function presidency(): ?self
    {
        return self::query()->where('presidency', true)->orderBy('id')->first();
    }

    /**
     * The people who work in the committee and so may get one of its bonuses: whoever sent it an evidence, attended one
     * of its meetings or coordinates it or keeps its minutes. The lecturers never get bonuses.
     */
    public function members(): Builder
    {
        return User::query()
            ->where(fn (Builder $query) => $query
                ->whereHas('evidences', fn (Builder $evidences) => $evidences->where('committee_id', $this->id)
                    ->where('last', true)->whereNotIn('status', ['DRAFT', 'BIN']))
                ->orWhereHas('meetings', fn (Builder $meetings) => $meetings->where('committee_id', $this->id))
                ->orWhereHas('coordinator', fn (Builder $coordinator) => $coordinator->where('committee_id', $this->id))
                ->orWhereHas('secretary', fn (Builder $secretary) => $secretary->where('committee_id', $this->id)))
            ->whereDoesntHave('roles', fn (Builder $roles) => $roles->where('rol', 'LECTURE'));
    }

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

    public function bonus()
    {
        return $this->hasMany(Bonus::class)->orderByDesc('created_at');
    }

    // Filtros de evidencias
    public function evidencesNotDraft()
    {
        return $this->evidences()->where('last', true)->where('status', '!=', 'DRAFT')->orderByDesc('updated_at');
    }

    public function evidencesDraft()
    {
        return $this->evidences()->where('last', true)->where('status', 'DRAFT')->orderByDesc('updated_at');
    }

    public function evidencesPending()
    {
        return $this->evidences()->where('last', true)->where('status', 'PENDING')->orderByDesc('updated_at');
    }

    public function evidencesAccepted()
    {
        return $this->evidences()->where('last', true)->where('status', 'ACCEPTED')->orderByDesc('updated_at');
    }

    public function evidencesRejected()
    {
        return $this->evidences()->where('last', true)->where('status', 'REJECTED')->orderByDesc('updated_at');
    }

    // Lógica de eliminación: el comité de presidencia nunca se borra, sus presidentes revisan en él
    public function can_be_removed(): bool
    {
        return
            ! $this->presidency &&
            $this->coordinators->isEmpty() &&
            $this->secretaries->isEmpty() &&
            $this->evidences->isEmpty() &&
            $this->meetings->isEmpty();
    }

    // Métodos auxiliares
    public function get_all_meeting_requests(): Collection
    {
        return $this->secretaries->flatMap(fn ($s) => $s->meetingRequests);
    }

    public function get_all_meeting_minutes(): Collection
    {
        return $this->secretaries->flatMap(fn ($s) => $s->meetingMinutes);
    }
}
