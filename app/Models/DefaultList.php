<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefaultList extends Model
{
    protected $table = 'default_lists';

    protected $fillable = [
        'id',
        'name',
        'secretary_id',
    ];

    /**
     * The users that belong to this default list.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * The secretary associated with this default list.
     */
    public function secretary()
    {
        return $this->belongsTo(Secretary::class);
    }
}
