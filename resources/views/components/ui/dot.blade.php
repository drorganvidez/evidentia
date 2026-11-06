{{--
    Status dot. Always decorative: the text beside it carries the meaning.
    <x-ui.dot tone="pending" pulse />      status colour with a pulsing halo
    <x-ui.dot :hue="150" />                committee colour, the hue rounded to a multiple of 30
--}}
@props(['tone' => 'draft', 'pulse' => false, 'size' => 'md', 'hue' => null])
@php
    $colour =
        $hue === null
            ? match ($tone) {
                'accepted' => 'bg-(--orb) orb-glow [--orb:var(--accepted)]',
                'pending' => 'bg-(--orb) orb-glow [--orb:var(--pending)]',
                'rejected' => 'bg-(--orb) orb-glow [--orb:var(--rejected)]',
                'notice' => 'bg-(--orb) orb-glow [--orb:var(--notice)]',
                'brand' => 'bg-(--orb) orb-glow [--orb:var(--brand)]',
                default => 'bg-(--orb) orb-glow [--orb:var(--draft)]',
            }
            : 'committee-dot hue-' . ((((int) round($hue / 30) * 30) % 360) + 360) % 360;
@endphp
<span aria-hidden="true"
    {{ $attributes->class([
        'inline-block shrink-0 rounded-full print:shadow-none',
        match ($size) {
            'sm' => 'size-2',
            'lg' => 'size-2.5',
            default => 'size-2.25',
        },
        $colour,
        'animate-pulse-orb' => $pulse && $hue === null,
    ]) }}></span>
