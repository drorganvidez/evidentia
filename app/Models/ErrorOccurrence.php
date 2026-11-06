<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One time an error happened: where, to whom and the reference the error page showed. */
class ErrorOccurrence extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(ErrorReport::class, 'error_report_id');
    }

    /** The short code of the error page (the first eight characters of the request id, uppercase). */
    public function code(): string
    {
        return strtoupper(substr($this->reference, 0, 8));
    }
}
