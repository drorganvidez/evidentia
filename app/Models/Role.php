<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * The users that belong to this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
