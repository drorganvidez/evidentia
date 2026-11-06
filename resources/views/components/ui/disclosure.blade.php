{{--
    Native details/summary: a title, an optional icon, something to its right and the content below. Several with the
    same name form an exclusive accordion. Nothing interactive goes inside the summary.
    <x-ui.disclosure title="Comité de Programa" icon="scroll-text" name="comites" :level="3">
        <x-slot:extra><x-ui.badge variant="count">4</x-ui.badge></x-slot:extra>
        ...
    </x-ui.disclosure>
    <x-ui.disclosure title="Ver ediciones anteriores" variant="plain" size="sm" open>...</x-ui.disclosure>
    The title tag is printed from PHP because blade-formatter does not follow dynamic tags.
--}}
@props([
    'title' => null,
    'level' => null,
    'open' => false,
    'name' => null,
    'icon' => null,
    'size' => 'md',
    'variant' => 'card',
])
@php
    throw_if(blank($title), InvalidArgumentException::class, 'The disclosure component needs a title.');
    $heading = $level !== null ? 'h' . max(1, min(6, (int) $level)) : 'span';
    [$summaryClasses, $bodyClasses, $titleClasses] = match ($size) {
        'sm' => ['min-h-11 gap-2.5 p-4', 'px-4 pb-4', 'min-w-0 text-sm font-semibold'],
        default => ['min-h-12 gap-3 p-5', 'px-5 pb-5', 'min-w-0 text-md font-semibold'],
    };
    $variantClasses = match ($variant) {
        'plain' => 'rounded-lg',
        default => 'glass rounded-lg open:shadow-raised',
    };
@endphp
<details {{ $attributes->class(['group', $variantClasses])->merge(array_filter(['name' => $name])) }}
    @if ($open) open @endif>
    <summary
        class="{{ Arr::toCssClasses(['flex cursor-pointer list-none items-center rounded-lg [&::-webkit-details-marker]:hidden', $summaryClasses]) }}">
        @if ($icon !== null)
            <x-ui.icon :name="$icon" class="size-4.5 text-ink-muted" />
        @endif
        {!! '<' . $heading . ' class="' . $titleClasses . '">' !!}{{ $title }}{!! '</' . $heading . '>' !!}
        <span class="ml-auto flex shrink-0 items-center gap-2">
            @isset($extra)
                {{ $extra }}
            @endisset
            <x-lucide-chevron-down aria-hidden="true" class="size-4 text-ink-muted transition group-open:rotate-180" />
        </span>
    </summary>
    <div class="{{ $bodyClasses }}">{{ $slot }}</div>
</details>
