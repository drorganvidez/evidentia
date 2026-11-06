{{--
    Status of an Eventbrite event as a badge: draft, live, started, ended, completed and canceled; an unknown literal
    is printed as it is (escaped) in the draft tone.
    <x-ui.event-status :status="$event->status" />
    <x-ui.event-status status="live" size="sm" />
    Its data-sort-value orders the browser tables along the life of the event.
--}}
@props(['status' => null, 'size' => 'md'])
@php
    [$tone, $text, $rank] = match ($status) {
        'draft' => ['draft', 'En borrador', 0],
        'live' => ['accepted', 'Publicado', 1],
        'started' => ['notice', 'En curso', 2],
        'ended' => ['notice', 'Finalizado', 3],
        'completed' => ['draft', 'Completado', 4],
        'canceled' => ['rejected', 'Cancelado', 5],
        default => ['draft', filled($status) ? (string) $status : 'Desconocido', 9],
    };
@endphp
<x-ui.badge :tone="$tone" :size="$size" :data-sort-value="$rank" {{ $attributes }}>{{ $text }}</x-ui.badge>
