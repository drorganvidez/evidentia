<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agreement extends Model
{
    protected $table = 'agreements';

    protected $fillable = [
        'point_id',
        'identificator',
        'description',
    ];

    /**
     * Get the point associated with the agreement.
     */
    public function point()
    {
        return $this->belongsTo(Point::class);
    }
}
