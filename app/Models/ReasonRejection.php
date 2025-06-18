<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReasonRejection extends Model
{
    protected $table = 'reason_rejections';

    protected $fillable = [
        'reason',
        'evidence_id',
    ];

    /**
     * Get the evidence this reason belongs to.
     */
    public function evidence()
    {
        return $this->belongsTo(Evidence::class);
    }
}
