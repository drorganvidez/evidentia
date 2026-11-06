{{--
    Collapsible filter bar: a native <details> whose summary counts the active filters, with a GET form inside.
    <x-ui.filter-bar :action="route('activity.index')" :open="$filters !== []" :active="count($filters)"
        :reset-href="route('activity.index')" :download="['href' => route('activity.export'), 'label' => 'Descargar CSV']">
        <x-ui.field name="type" label="Tipo" type="select" :options="$types" />
    </x-ui.filter-bar>
    The fields fill a grid of two columns from md and four from lg. The download button submits the same filters to
    download.href (so that url must not carry a query string) and keeps the form busy until the file arrives.
--}}
@props([
    'action' => '',
    'label' => 'Filtros',
    'open' => false,
    'active' => 0,
    'resetHref' => null,
    'download' => null,
    'id' => null,
])
@php
    throw_if(blank($action), InvalidArgumentException::class, 'The filter bar needs an action.');

    $active = (int) $active;
    $downloadHref = is_array($download) ? $download['href'] ?? null : null;
    $downloadLabel = is_array($download) ? $download['label'] ?? 'Descargar CSV' : null;
@endphp
<details {{ $attributes->class(['group glass rounded-xl']) }} @if ($open) open @endif
    @if ($id !== null) id="{{ $id }}" @endif>
    <summary
        class="flex min-h-12 cursor-pointer list-none items-center gap-3 px-5 py-3 text-md font-semibold [&::-webkit-details-marker]:hidden">
        <x-lucide-sliders-horizontal class="size-4.5" aria-hidden="true" />
        {{ $label }}
        @if ($active > 0)
            <x-ui.badge variant="count">{{ $active }}</x-ui.badge>
            <span class="sr-only">{{ $active === 1 ? 'filtro activo' : 'filtros activos' }}</span>
        @endif
        <x-lucide-chevron-down class="ml-auto size-4 transition group-open:rotate-180" aria-hidden="true" />
    </summary>
    <x-ui.form method="get" :action="$action" :summary="false" :download="$downloadHref !== null" busy-label="Aplicando"
        class="border-t border-rule p-5">
        <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2 lg:grid-cols-4">{{ $slot }}</div>
        <div class="flex flex-col gap-3 *:justify-center md:flex-row md:items-center">
            <x-ui.button type="submit" variant="primary" size="sm">Aplicar</x-ui.button>
            @if ($resetHref !== null)
                <x-ui.button variant="ghost" size="sm" :href="$resetHref">Limpiar</x-ui.button>
            @endif
            @if ($downloadHref !== null)
                <x-ui.button type="submit" size="sm" icon="download" formaction="{{ $downloadHref }}"
                    data-busy-label="Preparando" class="md:ml-auto">{{ $downloadLabel }}</x-ui.button>
            @endif
        </div>
    </x-ui.form>
</details>
