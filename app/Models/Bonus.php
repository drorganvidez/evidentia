<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $table = 'bonuses';

    protected $fillable = [
        'reason',
        'hours',
    ];

    /**
     * The users that received the bonus.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * The committee that granted the bonus.
     */
    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }
}
