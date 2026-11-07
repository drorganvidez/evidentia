<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $table = 'configuration';

    protected $fillable = [
        'event_name',
        'event_edition',
        'upload_evidences_timestamp',
        'validate_evidences_timestamp',
        'meetings_timestamp',
        'bonus_timestamp',
        'attendee_timestamp',
        'max_evidences_hours',
        'max_attendees_hours',
        'eventbrite_token',
        'events_uploaded_timestamp',
        'attendees_uploaded_timestamp',
    ];

    /** «Jornadas InnoSoft Days 2026»: the name of the event with its edition; null while the course has no name. */
    public static function eventTitle(): ?string
    {
        $configuration = self::query()->find(1, ['event_name', 'event_edition']);
        $title = trim($configuration?->event_name.' '.$configuration?->event_edition);

        return $title === '' ? null : $title;
    }
}
