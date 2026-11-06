<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One server error, grouped by its class, file and line, with its count and its latest occurrences. */
class ErrorReport extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'resolved_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    /** Named apart from the occurrences column (the count). */
    public function latestOccurrences(): HasMany
    {
        return $this->hasMany(ErrorOccurrence::class)->orderByDesc('occurred_at')->orderByDesc('id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->whereNotNull('resolved_at');
    }

    /** Short name of the exception class, for the lists. */
    public function shortException(): string
    {
        return class_basename($this->exception);
    }

    /** Path of the file inside the app, without the server directory. */
    public function relativeFile(): ?string
    {
        return $this->file === null ? null : ltrim(str_replace(base_path(), '', $this->file), '/');
    }
}
