<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = "reviews";

    protected $fillable = [
        'evidence_id',
        'score',
        'status',
        'comment'
    ];

    public function evidence()
    {
        return $this->belongsTo(Evidence::class);
    }
}
