{{--
    Status of an evidence as a badge: DRAFT, PENDING (pulsing), ACCEPTED, REJECTED and BIN; anything else reads
    «Desconocido». With REJECTED the reason goes to the title (the view shows it in full in an alert).
    <x-ui.evidence-status :status="$evidence->status" />
    <x-ui.evidence-status status="REJECTED" :reason="$evidence->reason" size="sm" />
    Its data-sort-value orders the browser tables by the review: draft, pending, accepted, rejected, removed.
--}}
@props(['status' => null, 'size' => 'md', 'reason' => null])
@php
    [$tone, $text, $pulse, $rank] = match ($status) {
        'DRAFT' => ['draft', 'En borrador', false, 0],
        'PENDING' => ['pending', 'Pendiente de revisión', true, 1],
        'ACCEPTED' => ['accepted', 'Aceptada', false, 2],
        'REJECTED' => ['rejected', 'Rechazada', false, 3],
        'BIN' => ['draft', 'Eliminada', false, 4],
        default => ['draft', 'Desconocido', false, 9],
    };
    $title = $status === 'REJECTED' && filled($reason) ? (string) $reason : null;
@endphp
<x-ui.badge :tone="$tone" :pulse="$pulse" :size="$size" :title="$title" :data-sort-value="$rank"
    {{ $attributes }}>{{ $text }}</x-ui.badge>
