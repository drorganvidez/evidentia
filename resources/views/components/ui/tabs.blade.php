{{--
    Filters or page tabs driven by URLs: the server filters, the current link carries aria-current. No tablist and no
    script; on the phone the pill scrolls without a bar.
    <x-ui.tabs label="Filtrar por estado" :items="[
        ['label' => 'Todas', 'href' => '?estado=todas', 'count' => 24, 'current' => true],
        ['label' => 'Pendientes', 'href' => '?estado=pendientes', 'count' => 2],
    ]" />
    <x-ui.tabs label="Secciones del perfil" :items="$tabs" size="md" />
--}}
@props(['items' => [], 'label' => null, 'size' => 'sm'])
@php
    throw_if(blank($label), InvalidArgumentException::class, 'The tabs component needs a label.');
    $linkClasses = Arr::toCssClasses([
        'inline-flex items-center gap-1.5 rounded-full font-semibold whitespace-nowrap text-ink-muted no-underline transition scroll-ml-4 current:bg-glass-2 current:text-ink current:shadow-raised hover:text-ink focus-visible:-outline-offset-2 pointer-coarse:min-h-11',
        match ($size) {
            'md' => 'px-4 py-2 text-md',
            default => 'px-3.5 py-1.75 text-sm',
        },
    ]);
@endphp
<nav aria-label="{{ $label }}" {{ $attributes->class(['max-w-full']) }}>
    <ul
        class="inline-flex max-w-full gap-0.5 overflow-x-auto rounded-full border border-glass-edge bg-field p-0.75 no-scrollbar max-md:w-full">
        @foreach ($items as $item)
            <li class="shrink-0">
                <a href="{{ $item['href'] }}" class="{{ $linkClasses }}"
                    @if (!empty($item['current'])) aria-current="page" @endif>
                    {{ $item['label'] }}
                    @if (isset($item['count']))
                        <x-ui.badge variant="count">{{ $item['count'] }}</x-ui.badge>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</nav>
