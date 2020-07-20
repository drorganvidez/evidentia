<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $table = "configuration";

    protected $fillable = ["upload_evidences_timestamp", "validate_evidences_timestamp", "meetings_timestamp", "bonus_timestamp", "attendee_timestamp", "eventbrite_token","events_uploaded_timestamp","attendees_uploaded_timestamp"];
}
