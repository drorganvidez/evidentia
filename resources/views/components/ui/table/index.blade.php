{{--
    Data table. Sorting and pagination live in the URL (the server orders); the quick filter runs in the browser
    and only when the whole collection is in the DOM (filterable, never together with pagination).
    Explicit roles keep the semantics when the rows turn into cards on the phone.
    <x-ui.table caption="Mis evidencias" :columns="[
        ['label' => 'Evidencia', 'key' => 'title', 'sort' => ['href' => '?sort=title', 'dir' => null]],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Abrir', 'srOnly' => true],
    ]" filterable>
        <x-slot:toolbar>...</x-slot:toolbar>
        <x-ui.table.row :href="route('evidence.view', $evidence)">
            <x-ui.table.cell label="Evidencia" primary><a href="...">{{ $evidence->title }}</a></x-ui.table.cell>
        </x-ui.table.row>
        <x-slot:footer>...</x-slot:footer>
        <x-slot:empty><x-ui.empty title="No hay evidencias" size="sm" /></x-slot:empty>
    </x-ui.table>
    A column is ['label', 'key', 'align' => start|end, 'srOnly' => bool, 'hide' => bool, 'sort' => ['href', 'dir' => asc|desc|null], 'class'].
    columns takes an array or a collection; an Arrayable column is read through toArray().
--}}
@use('Illuminate\Contracts\Support\Arrayable')
@props([
    'caption' => null,
    'showCaption' => false,
    'columns' => [],
    'density' => 'comfortable',
    'cards' => true,
    'filterable' => false,
    'filterPlaceholder' => 'Filtrar',
    'countLabel' => 'filas',
    'id' => null,
])
@php
    throw_if(blank($caption), InvalidArgumentException::class, 'The table component needs a caption.');
    $columns = collect($columns)
        ->map(fn($column) => $column instanceof Arrayable ? $column->toArray() : $column)
        ->values()
        ->all();
    $rowHeight = match ($density) {
        'compact' => '[--row-h:3.5rem]',
        default => '[--row-h:4.25rem]',
    };
    $toolbarClasses = 'flex flex-wrap items-center gap-2 border-b border-rule p-3 md:gap-3 md:px-5 md:py-4';
    $toolbarAttributes = isset($toolbar) ? $toolbar->attributes : new \Illuminate\View\ComponentAttributeBag();
    $headerClasses = fn(array $column, bool $sortable): string => Arr::toCssClasses([
        'px-5 py-3.5 text-xs font-semibold tracking-wider whitespace-nowrap text-ink-muted uppercase',
        ($column['align'] ?? 'start') === 'end' ? 'text-right' : 'text-left',
        'pointer-coarse:py-0' => $sortable,
        $column['class'] ?? '' => !empty($column['class']),
    ]);
    $sortTitle = fn(?string $dir): string => $dir === 'asc'
        ? 'Ordenar de forma descendente'
        : 'Ordenar de forma ascendente';
@endphp
<section
    {{ $attributes->class(['glass overflow-hidden rounded-xl', $rowHeight])->merge(array_filter(['aria-label' => $caption, 'id' => $id])) }}>
    @if (isset($toolbar) || $filterable)
        <div
            {{ $toolbarAttributes->class([$toolbarClasses])->merge($filterable ? ['x-data' => 'tableFilter', 'data-count-label' => $countLabel] : []) }}>
            @isset($toolbar)
                {{ $toolbar }}
            @endisset
            @if ($filterable)
                <input type="search" x-model="query" x-on:input.debounce.150ms="filter" autocomplete="off"
                    placeholder="{{ $filterPlaceholder }}" aria-label="{{ $filterPlaceholder }}"
                    class="h-9 w-0 min-w-0 flex-1 rounded-full border border-field-edge bg-field px-4 text-control text-ink placeholder:text-ink-muted md:ml-auto md:max-w-64 pointer-coarse:min-h-11">
                <span role="status" class="text-sm text-ink-muted tabular-nums" x-text="summary"></span>
            @endif
        </div>
    @endif
    <div x-data="scrollRegion" x-bind:tabindex="tabindex" x-bind:role="role" x-bind:aria-label="label"
        class="overflow-x-auto focus-visible:-outline-offset-3">
        <table role="table" @class(['w-full border-collapse text-md', 'block md:table' => $cards]) @if ($cards) data-cards @endif>
            <caption @class([
                'px-5 pt-4 pb-2 text-left text-lg font-bold tracking-tight' => $showCaption,
                'block md:table-caption' => $showCaption && $cards,
                'sr-only' => !$showCaption,
            ])>
                {{ $caption }}</caption>
            @if ($columns !== [])
                <thead role="rowgroup" @if ($cards) class="hidden md:table-header-group" @endif>
                    <tr role="row" class="border-b border-rule">
                        @foreach ($columns as $column)
                            @php
                                $sort = $column['sort'] ?? null;
                                $dir = is_array($sort) ? $sort['dir'] ?? null : null;
                                $sortable = is_array($sort) && !empty($sort['href']);
                            @endphp
                            <th role="columnheader" scope="col" class="{{ $headerClasses($column, $sortable) }}"
                                @if ($sortable && $dir !== null) aria-sort="{{ $dir === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                @if ($sortable)
                                    <a href="{{ $sort['href'] }}" title="{{ $sortTitle($dir) }}"
                                        class="inline-flex min-h-6 items-center gap-1 text-ink pointer-coarse:min-h-11">
                                        {{ $column['label'] ?? '' }}
                                        @if ($dir === 'desc')
                                            <x-lucide-arrow-down aria-hidden="true" class="size-3.5" />
                                        @elseif ($dir === 'asc')
                                            <x-lucide-arrow-up aria-hidden="true" class="size-3.5" />
                                        @else
                                            <x-lucide-arrow-up-down aria-hidden="true" class="size-3.5 opacity-55" />
                                        @endif
                                    </a>
                                @elseif (!empty($column['srOnly']))
                                    <span class="sr-only">{{ $column['label'] ?? '' }}</span>
                                @else
                                    {{ $column['label'] ?? '' }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody role="rowgroup" @if ($cards) class="block md:table-row-group" @endif>
                @isset($empty)
                    <tr role="row" @if ($cards) class="block md:table-row" @endif>
                        <td role="cell" colspan="{{ max(1, count($columns)) }}" @class(['p-0', 'block md:table-cell' => $cards])>
                            {{ $empty }}</td>
                    </tr>
                @else
                    {{ $slot }}
                @endisset
            </tbody>
        </table>
    </div>
    @isset($footer)
        <div
            {{ $footer->attributes->class(['flex flex-col items-start gap-2 border-t border-rule px-5 py-4 text-sm text-ink-muted md:flex-row md:items-center md:justify-between']) }}>
            {{ $footer }}</div>
    @endisset
</section>
