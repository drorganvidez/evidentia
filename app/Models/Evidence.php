<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{
    protected $table = 'evidences';

    protected $fillable = [
        'title',
        'description',
        'hours',
        'user_id',
        'committee_id',
        'points_to',
        'status',
        'stamp',
        'rand',
        'last',
    ];

    // Relaciones
    public function proofs()
    {
        return $this->hasMany(Proof::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    // el último motivo de rechazo (una evidencia puede rechazarse varias veces)
    public function reasonRejection()
    {
        return $this->hasOne(ReasonRejection::class)->latestOfMany();
    }

    public function reasonRejections()
    {
        return $this->hasMany(ReasonRejection::class)->orderBy('id');
    }

    // Evidencias anteriores (recursivo)
    public function previousEvidences()
    {
        $previous = static::find($this->points_to);

        if (! $previous) {
            return collect([$this]);
        }

        return $this->previousEvidencesRecursive($previous, collect());
    }

    private function previousEvidencesRecursive($evidence, $collection)
    {
        $collection->push($evidence);

        if (is_null($evidence->points_to)) {
            return $collection;
        }

        $previous = static::find($evidence->points_to);

        return $this->previousEvidencesRecursive($previous, $collection);
    }

    // Evidencias posteriores (recursivo)
    public function laterEvidences()
    {
        $next = static::where('points_to', $this->id)->first();

        if (! $next) {
            return collect([$this]);
        }

        return $this->laterEvidencesRecursive($next, collect());
    }

    private function laterEvidencesRecursive($evidence, $collection)
    {
        $collection->push($evidence);

        if ($evidence->last ?? false) {
            return $collection;
        }

        $next = static::where('points_to', $evidence->id)->first();

        return $this->laterEvidencesRecursive($next, $collection);
    }

    // Flujo completo
    public function flowEvidences()
    {
        return $this->previousEvidences()
            ->concat([$this])
            ->concat($this->laterEvidences())
            ->unique()
            ->sortByDesc('created_at');
    }

    // Encuentra la última del flujo
    public function findHeaderEvidence()
    {
        return $this->findHeaderEvidenceRecursive($this);
    }

    private function findHeaderEvidenceRecursive($evidence)
    {
        if ($evidence->last ?? false) {
            return $evidence;
        }

        $next = static::where('points_to', $evidence->id)->first();

        return $this->findHeaderEvidenceRecursive($next);
    }

    // primera versión de la cadena; todas las versiones comparten su historial
    public function rootEvidence(): Evidence
    {
        return $this->ancestors()->last() ?? $this;
    }

    // última versión de la cadena, o la propia evidencia si la cadena está rota
    public function headEvidence(): Evidence
    {
        $head = $this;
        $seen = [$this->id => true];

        while (! $head->last) {
            $next = static::where('points_to', $head->id)->first();
            if (! $next || isset($seen[$next->id])) {
                return $this;
            }
            $seen[$next->id] = true;
            $head = $next;
        }

        return $head;
    }

    // número de versión empezando por 1
    public function versionNumber(): int
    {
        return $this->ancestors()->count() + 1;
    }

    // versiones anteriores, de la más cercana a la primera; a prueba de cadenas rotas o circulares
    private function ancestors()
    {
        $ancestors = collect();
        $current = $this;
        $seen = [$this->id => true];

        while ($current->points_to !== null && ($previous = static::find($current->points_to)) && ! isset($seen[$previous->id])) {
            $seen[$previous->id] = true;
            $ancestors->push($previous);
            $current = $previous;
        }

        return $ancestors;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'DRAFT' => 'En borrador',
            'PENDING' => 'Pendiente de revisión',
            'ACCEPTED' => 'Aceptada',
            'REJECTED' => 'Rechazada',
            'BIN' => 'Eliminada',
            default => 'Desconocido',
        };
    }

    // Scopes estáticos
    public static function evidencesNotDraft()
    {
        return static::where('last', true)->where('status', '!=', 'DRAFT')->orderByDesc('updated_at')->get();
    }

    public static function evidencesDraft()
    {
        return static::where('last', true)->where('status', 'DRAFT')->whereNull('points_to')->orderByDesc('updated_at')->get();
    }

    public static function evidencesPending()
    {
        return static::where('last', true)->where('status', 'PENDING')->orderByDesc('updated_at')->get();
    }

    public static function evidencesAccepted()
    {
        return static::where('last', true)->where('status', 'ACCEPTED')->orderByDesc('updated_at')->get();
    }

    public static function evidencesRejected()
    {
        return static::where('last', true)->where('status', 'REJECTED')->orderByDesc('updated_at')->get();
    }

    public function integrity(): bool
    {
        return $this->stamp === \Stamp::get_stamp_evidence($this);
    }

    public function allRejectionReasons()
    {
        return $this->previousEvidences()
            ->push($this)
            ->unique('id')
            ->sortBy('id')
            ->flatMap(fn ($ev) => $ev->reasonRejections)
            ->unique('id')
            ->values();
    }
}
