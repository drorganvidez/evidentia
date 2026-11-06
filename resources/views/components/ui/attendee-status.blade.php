{{--
    Attendance of an Eventbrite attendee as a badge, from the literal status Eventbrite stores; an unknown literal is
    printed as it is (escaped) in the draft tone.
    <x-ui.attendee-status :status="$attendee->status" />
    <x-ui.attendee-status status="Checked In" size="sm" />
--}}
@props(['status' => null, 'size' => 'md'])
@php
    [$tone, $text] = match ($status) {
        'Attending' => ['pending', 'Pendiente de asistir'],
        'Checked In' => ['accepted', 'Asistido'],
        'Guests Attended' => ['accepted', 'Asistido (invitado)'],
        'Guests Attending' => ['pending', 'Pendiente de asistir (invitado)'],
        'Not Attending (Refunded/Canceled)' => ['rejected', 'Asistencia cancelada'],
        'Not Attending' => ['rejected', 'No asistido'],
        default => ['draft', filled($status) ? (string) $status : 'Desconocido'],
    };
@endphp
<x-ui.badge :tone="$tone" :size="$size" {{ $attributes }}>{{ $text }}</x-ui.badge>
