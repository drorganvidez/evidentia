<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name', // o cualquier otro campo que uses, como 'slug', 'description', etc.
    ];

    /**
     * The users that belong to this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
