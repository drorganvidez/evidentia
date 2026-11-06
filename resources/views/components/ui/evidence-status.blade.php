{{--
    Status of an evidence as a badge: DRAFT, PENDING (pulsing), ACCEPTED, REJECTED and BIN; anything else reads
    «Desconocido». With REJECTED the reason goes to the title (the view shows it in full in an alert).
    <x-ui.evidence-status :status="$evidence->status" />
    <x-ui.evidence-status status="REJECTED" :reason="$evidence->reason" size="sm" />
--}}
@props(['status' => null, 'size' => 'md', 'reason' => null])
@php
    [$tone, $text, $pulse] = match ($status) {
        'DRAFT' => ['draft', 'En borrador', false],
        'PENDING' => ['pending', 'Pendiente de revisión', true],
        'ACCEPTED' => ['accepted', 'Aceptada', false],
        'REJECTED' => ['rejected', 'Rechazada', false],
        'BIN' => ['draft', 'Eliminada', false],
        default => ['draft', 'Desconocido', false],
    };
    $title = $status === 'REJECTED' && filled($reason) ? (string) $reason : null;
@endphp
<x-ui.badge :tone="$tone" :pulse="$pulse" :size="$size" :title="$title"
    {{ $attributes }}>{{ $text }}</x-ui.badge>
