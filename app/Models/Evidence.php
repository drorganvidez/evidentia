<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Proof;
use App\Models\User;
use App\Models\Committee;
use App\Models\ReasonRejection;

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
        'last'
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

    public function reasonRejection()
    {
        return $this->hasOne(ReasonRejection::class);
    }

    // Evidencias anteriores (recursivo)
    public function previousEvidences()
    {
        $previous = static::find($this->points_to);

        if (!$previous) {
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

        if (!$next) {
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

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'DRAFT'    => 'En borrador',
            'PENDING'  => 'Pendiente de revisión',
            'ACCEPTED' => 'Aceptada',
            'REJECTED' => 'Rechazada',
            'BIN'      => 'Eliminada',
            default    => 'Desconocido',
        };
    }

    // Scopes estáticos
    public static function evidencesNotDraft()
    {
        return static::where('status', '!=', 'DRAFT')->orderByDesc('updated_at')->get();
    }

    public static function evidencesDraft()
    {
        return static::where('status', 'DRAFT')->whereNull('points_to')->orderByDesc('updated_at')->get();
    }

    public static function evidencesPending()
    {
        return static::where('status', 'PENDING')->orderByDesc('updated_at')->get();
    }

    public static function evidencesAccepted()
    {
        return static::where('status', 'ACCEPTED')->orderByDesc('updated_at')->get();
    }

    public static function evidencesRejected()
    {
        return static::where('status', 'REJECTED')->orderByDesc('updated_at')->get();
    }

    public function integrity(): bool
    {
        return $this->stamp === \Stamp::get_stamp_evidence($this);
    }
}
