{{--
    One pair of x-ui.dl: the term on the left, the description on the right. The width of the term and the row
    padding come from the parent list (--term-w, --dl-py).
    <x-ui.dl.item term="Horas"><x-ui.hours :value="1.75" /></x-ui.dl.item>
    <x-ui.dl.item term="Registro" mono>EV-0033</x-ui.dl.item>
--}}
@props(['term' => null, 'mono' => false])
@php
    throw_if(blank($term), InvalidArgumentException::class, 'The dl item needs a term.');
@endphp
<div
    {{ $attributes->class(['grid grid-cols-[var(--term-w)_minmax(0,1fr)] items-center gap-3 border-t border-rule py-(--dl-py) text-md']) }}>
    <dt class="font-medium text-ink-muted">{{ $term }}</dt>
    <dd @class(['m-0 min-w-0', 'font-mono tabular-nums' => $mono])>{{ $slot }}</dd>
</div>
