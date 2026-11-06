{{--
    Figure chip of the access page: a status dot, a number that counts up (countUp.js) and its unit.
    <x-ui.count-chip :value="1204" unit="h acreditadas" />
    <x-ui.count-chip :value="10.5" :decimals="1" unit="h de media" tone="accepted" :delay="80" />
    The server prints the final figure (it reads right without JS) and the whole chip is named by it; delay is one of
    0, 80, 160, 240 or 320 ms so the classes stay literal. rise=false skips the entrance animation (the catalogue
    audits contrast while a rising chip is still translucent).
--}}
@props([
    'value' => null,
    'unit' => null,
    'tone' => 'brand',
    'delay' => 0,
    'decimals' => 0,
    'duration' => 1200,
    'rise' => true,
])
@php
    throw_if(!is_numeric($value), InvalidArgumentException::class, 'The count chip needs a numeric value.');
    throw_if(blank($unit), InvalidArgumentException::class, 'The count chip needs a unit.');
    $formatted = number_format((float) $value, (int) $decimals, ',', '.');
    $delay = (int) $delay;
@endphp
<span role="img" aria-label="{{ $formatted }} {{ $unit }}"
    {{ $attributes->class([
        'glass inline-flex items-center gap-3 rounded-full py-2.5 pr-4 pl-3 text-md font-medium text-ink max-md:py-2 max-md:pr-3 max-md:pl-2.5 max-md:text-sm',
        'animate-rise motion-reduce:animate-none' => $rise,
        '[animation-delay:80ms]' => $rise && $delay === 80,
        '[animation-delay:160ms]' => $rise && $delay === 160,
        '[animation-delay:240ms]' => $rise && $delay === 240,
        '[animation-delay:320ms]' => $rise && $delay === 320,
    ]) }}>
    <x-ui.dot :tone="$tone" size="lg" />
    <span x-data="countUp" data-count="{{ $value }}" data-decimals="{{ (int) $decimals }}"
        data-duration="{{ (int) $duration }}" class="font-mono text-lg font-semibold tracking-tight tabular-nums"
        x-text="shown">{{ $formatted }}</span>
    {{ $unit }}
</span>
