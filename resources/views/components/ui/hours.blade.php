{{--
    Hours with the Spanish decimal comma and tabular figures; the raw value goes to the title when rounding hides decimals.
    <x-ui.hours :value="$evidence->hours" />                      1,75 h
    <x-ui.hours :value="$total" size="xl" :decimals="1" />         1.204,5 h
    <x-ui.hours :value="null" null-text="Sin horas" muted />
--}}
@props([
    'value' => null,
    'decimals' => 2,
    'unit' => ' h',
    'size' => 'md',
    'muted' => false,
    'nullText' => '—',
])
@php
    $sizeClass = match ($size) {
        'sm' => 'text-sm',
        'lg' => 'text-lg',
        'xl' => 'text-4xl',
        default => 'text-md',
    };
    $number = is_numeric($value) ? (float) $value : null;
    $loses = $number !== null && abs(round($number, $decimals) - $number) > 1e-9;
    $title = $loses ? (is_string($value) ? trim($value) : (string) $value) : null;
@endphp
@if ($number === null)
    <span {{ $attributes->class(['font-mono tabular-nums text-ink-muted', $sizeClass]) }}>{{ $nullText }}</span>
@else
    <span
        {{ $attributes->class(['font-mono tabular-nums whitespace-nowrap', $sizeClass, 'text-ink-muted' => $muted])->merge(array_filter(['title' => $title])) }}>{{ number_format($number, $decimals, ',', '.') }}{{ $unit }}</span>
@endif
