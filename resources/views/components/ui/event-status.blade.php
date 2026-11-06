{{--
    Status of an Eventbrite event as a badge: draft, live, started, ended, completed and canceled; an unknown literal
    is printed as it is (escaped) in the draft tone.
    <x-ui.event-status :status="$event->status" />
    <x-ui.event-status status="live" size="sm" />
--}}
@props(['status' => null, 'size' => 'md'])
@php
    [$tone, $text] = match ($status) {
        'draft' => ['draft', 'En borrador'],
        'live' => ['accepted', 'Publicado'],
        'started' => ['notice', 'En curso'],
        'ended' => ['notice', 'Finalizado'],
        'completed' => ['draft', 'Completado'],
        'canceled' => ['rejected', 'Cancelado'],
        default => ['draft', filled($status) ? (string) $status : 'Desconocido'],
    };
@endphp
<x-ui.badge :tone="$tone" :size="$size" {{ $attributes }}>{{ $text }}</x-ui.badge>
