{{--
    Figure card of the dashboard: a label, a formatted value, a unit and a foot line; one link when it has an href.
    <x-ui.stat label="Pendientes" value="3" foot="evidencias por revisar" tone="pending" href="/evidencias" mobile="hide" />
    <x-ui.stat label="Horas aceptadas" value="4,00" unit="h" foot="de 8,25 h declaradas" href="/horas" main>
        <x-slot:aside><x-ui.meter shape="ring" ... /></x-slot:aside>
        <x-slot:legend>...</x-slot:legend>   from md
        <x-slot:mini>...</x-slot:mini>       on the phone
    </x-ui.stat>
    With href the whole card is one link named by its content, so nothing inside it may be interactive; an ariaLabel
    is only right when it repeats the visible text (WCAG 2.5.3). The default slot prints under the foot (a meter).
    The root tag is printed from PHP because blade-formatter does not follow dynamic tags.
--}}
@props([
    'label' => null,
    'value' => null,
    'unit' => null,
    'foot' => null,
    'tone' => null,
    'href' => null,
    'main' => false,
    'ariaLabel' => null,
    'mobile' => 'show',
])
@php
    throw_if(
        blank($label) || $value === null || $value === '',
        InvalidArgumentException::class,
        'The stat component needs a label and a value.',
    );
    $tag = $href !== null ? 'a' : 'div';
    $root = $attributes
        ->class([
            'glass group relative grid min-h-0 gap-4 rounded-xl p-5 text-inherit no-underline md:min-h-42 md:p-6',
            'transition hover:-translate-y-0.5' => $href !== null,
            'md:grid-cols-[minmax(0,1fr)_auto] md:items-center' => $main,
            'max-md:hidden' => $mobile === 'hide',
        ])
        ->merge(array_filter(['href' => $href, 'aria-label' => $href !== null ? $ariaLabel : null]));
@endphp
{!! '<' . $tag . ' ' . $root . '>' !!}
@if ($href !== null)
    <x-lucide-chevron-right aria-hidden="true"
        class="absolute top-3.5 right-3.5 size-4.5 text-ink-muted opacity-55 transition group-hover:translate-x-0.5 group-hover:opacity-100" />
@endif
<div class="grid min-w-0 content-between gap-3 self-stretch">
    <span class="flex items-center gap-2 pr-6 text-sm font-semibold text-ink-muted">
        @if ($tone !== null)
            <x-ui.dot :tone="$tone" />
        @endif
        {{ $label }}
    </span>
    <div class="grid gap-1.5">
        <span class="flex flex-wrap items-baseline gap-x-1.5">
            <span
                class="font-mono text-3xl font-semibold tracking-tighter tabular-nums md:text-4xl">{{ $value }}</span>
            @if ($unit !== null)
                <span class="text-lg font-medium text-ink-muted">{{ $unit }}</span>
            @endif
        </span>
        @if ($foot !== null)
            <span class="block text-sm font-medium tracking-normal text-ink-muted">{{ $foot }}</span>
        @endif
        @isset($legend)
            <span
                {{ $legend->attributes->class(['mt-1 hidden flex-wrap gap-x-4 gap-y-2 text-xs text-ink-muted md:flex']) }}>{{ $legend }}</span>
        @endisset
        @isset($mini)
            <span
                {{ $mini->attributes->class(['mt-1.5 flex flex-wrap gap-x-3 gap-y-2 text-sm font-medium text-ink-muted md:hidden']) }}>{{ $mini }}</span>
        @endisset
        @unless ($slot->isEmpty())
            <div class="mt-2">{{ $slot }}</div>
        @endunless
    </div>
</div>
@isset($aside)
    <div {{ $aside->attributes->class(['justify-self-start md:justify-self-end']) }}>{{ $aside }}</div>
@endisset
{!! '</' . $tag . '>' !!}
