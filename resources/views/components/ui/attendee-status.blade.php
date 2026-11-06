{{--
    Attendance of an Eventbrite attendee as a badge, from the literal status Eventbrite stores; an unknown literal is
    printed as it is (escaped) in the draft tone.
    <x-ui.attendee-status :status="$attendee->status" />
    <x-ui.attendee-status status="Checked In" size="sm" />
    Its data-sort-value orders the browser tables as pending, attended, not attended, canceled.
--}}
@props(['status' => null, 'size' => 'md'])
@php
    [$tone, $text, $rank] = match ($status) {
        'Attending' => ['pending', 'Pendiente de asistir', 0],
        'Guests Attending' => ['pending', 'Pendiente de asistir (invitado)', 1],
        'Checked In' => ['accepted', 'Asistido', 2],
        'Guests Attended' => ['accepted', 'Asistido (invitado)', 3],
        'Not Attending' => ['rejected', 'No asistido', 4],
        'Not Attending (Refunded/Canceled)' => ['rejected', 'Asistencia cancelada', 5],
        default => ['draft', filled($status) ? (string) $status : 'Desconocido', 9],
    };
@endphp
<x-ui.badge :tone="$tone" :size="$size" :data-sort-value="$rank" {{ $attributes }}>{{ $text }}</x-ui.badge>
