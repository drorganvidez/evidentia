<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Diary;

class DiaryPoint extends Model
{
    protected $table = 'diary_points';

    protected $fillable = [
        'diary_id',
        'point',
    ];

    /**
     * Get the diary this point belongs to.
     */
    public function diary()
    {
        return $this->belongsTo(Diary::class);
    }
}
