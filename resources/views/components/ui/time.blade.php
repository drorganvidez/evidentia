{{--
    Date with its relative form in Spanish, the absolute one for the record and a machine-readable datetime.
    <x-ui.time :value="$evidence->created_at" />                             hace 6 horas, 05/11/2026 22:40 below
    <x-ui.time :value="$meeting->date" format="absolute" pattern="d/m/Y" />   05/11/2026
    <x-ui.time :value="$log->created_at" inline short />                     hace 6 horas 05/11 22:40 on one line
--}}
@use('Illuminate\Support\Carbon')
@props([
    'value' => null,
    'format' => 'both',
    'pattern' => 'd/m/Y H:i',
    'inline' => false,
    'mono' => true,
    'short' => false,
])
@php
    // The locale is forced on this copy of the date: the app may run in English and the tests always do.
    $date = $value === null || $value === '' ? null : Carbon::parse($value)->locale('es');
    $format = in_array($format, ['relative', 'absolute'], true) ? $format : 'both';
    $absolute = $date?->translatedFormat($pattern);
    $shown = $short && $date?->isCurrentYear() ? $date->translatedFormat('d/m H:i') : $absolute;
    $absoluteClass = Arr::toCssClasses([
        'font-mono tabular-nums' => $mono,
        'ml-1.5 text-ink-muted' => $format === 'both' && $inline,
        'block text-xs text-ink-muted' => $format === 'both' && !$inline,
    ]);
@endphp
@if ($date === null)
    <span {{ $attributes->class(['text-ink-faint']) }} data-sort-value="">
        —<span class="sr-only">Sin fecha</span></span>
@elseif ($format === 'absolute')
    <time
        {{ $attributes->merge(array_filter(['class' => $absoluteClass, 'datetime' => $date->toIso8601String()])) }}>{{ $shown }}</time>
@else
    <time
        {{ $attributes->merge(['datetime' => $date->toIso8601String(), 'title' => $absolute]) }}>{{ $date->diffForHumans() }}
        @if ($format === 'both')
            <span class="{{ $absoluteClass }}">{{ $shown }}</span>
        @endif
    </time>
@endif
