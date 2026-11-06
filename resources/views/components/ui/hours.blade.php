{{--
    Hours with the Spanish decimal comma and tabular figures. When the rounding hides decimals, screen readers read the
    exact value (up to four decimals) and the pointer shows it on hover; the rounded figure is only painted.
    <x-ui.hours :value="$evidence->hours" />                      1,75 h
    <x-ui.hours :value="$total" size="xl" :decimals="1" />         1.204,5 h
    <x-ui.hours :value="null" null-text="Sin horas" muted />
    The figure is also the data-sort-value the browser order of x-ui.table reads.
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
    // Trailing zeros of the decimal part go away so that 4,00 and 4 compare equal
    $trim = fn(string $text): string => str_contains($text, ',') ? rtrim(rtrim($text, '0'), ',') : $text;
    $shown = $number !== null ? number_format($number, $decimals, ',', '.') : null;
    $exact = $number !== null ? $trim(number_format($number, max(4, (int) $decimals), ',', '.')) : null;
    $loses = $number !== null && $exact !== $trim($shown);
@endphp
@if ($number === null)
    <span {{ $attributes->class(['font-mono tabular-nums text-ink-muted', $sizeClass]) }}
        data-sort-value="">{{ $nullText }}</span>
@elseif ($loses)
    <span {{ $attributes->class(['font-mono tabular-nums whitespace-nowrap', $sizeClass, 'text-ink-muted' => $muted]) }}
        data-sort-value="{{ $number }}">
        <span aria-hidden="true" title="{{ $exact . $unit }}">{{ $shown . $unit }}</span>
        <span class="sr-only">{{ $exact . $unit }}</span>
    </span>
@else
    <span {{ $attributes->class(['font-mono tabular-nums whitespace-nowrap', $sizeClass, 'text-ink-muted' => $muted]) }}
        data-sort-value="{{ $number }}">{{ $shown }}{{ $unit }}</span>
@endif
