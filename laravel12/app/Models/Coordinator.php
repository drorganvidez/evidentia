<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Committee;
use App\Models\User;

class Coordinator extends Model
{
    protected $table = 'coordinators';

    public function committee()
    {
        return $this->belongsTo(Committee::class, 'committee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
