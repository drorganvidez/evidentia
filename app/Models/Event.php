<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = "event";

    protected $fillable = ['name','description','id_eventbrite','start_datetime','end_datetime','capacity','status','url','hours'];

    public function attendees()
    {
        return $this->hasMany('App\Models\Attendee');
    }

}
