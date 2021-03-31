<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendee extends Model
{
    protected $table = "attendee";

    protected $fillable = ["event_id","user_id","status"];

    public function event()
    {
        return $this->belongsTo('App\Models\Event');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
