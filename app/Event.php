<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = "event";

    protected $fillable = ['name','description','id_eventbrite','start_datetime','end_datetime','capacity','status','url'];

    public function attendees()
    {
        return $this->hasMany('App\Attendee');
    }

}
