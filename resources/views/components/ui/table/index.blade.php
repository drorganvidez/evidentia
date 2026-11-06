{{--
    Data table. Sorting and pagination live in the URL (the server orders and paginates). Without a paginator the
    quick filter (filterable) runs in the browser over the whole collection in the DOM; with one it becomes a GET
    search (q) the server runs over the columns each screen chooses, and the footer prints the pagination and the
    rows per page (10, 25 or 50) after the slot of the footer, which keeps the totals of the whole result.
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
    <x-ui.table caption="Evidencias del curso" :columns="$columns" :paginator="$evidences" filterable
        filter-placeholder="Buscar evidencias" count-label="evidencias" count-singular="evidencia">...</x-ui.table>
    A column is ['label', 'key', 'align' => start|end, 'srOnly' => bool, 'hide' => bool, 'sort' => ['href', 'dir' => asc|desc|null,
    'asc', 'desc'], 'sortable' => bool, 'sorted' => asc|desc, 'class'].
    columns takes an array or a collection; an Arrayable column is read through toArray(). The paginator is a
    LengthAwarePaginator (App\Support\ListPage::paginate); the search form resends every other key of its query
    string and the size links go back to the first page; its count says count-singular for a single row.
    Order on the server: the columns come from App\Support\ListSort::columns(), whose sort links keep the query string
    and go back to the first page. Order in the browser (sortable, only without a paginator or server links): every
    column with a visible label is a header button with aria-sort, except the srOnly ones (actions) and those with
    'sortable' => false; 'sorted' marks the column the server already ordered the rows by. A cell orders by its
    :sort-value (the ISO instant of a date), by the data-sort-value of the hours, states and people inside it, by
    its <time datetime> or by its visible text (resources/js/components/tableSort.js), and it lives with the quick
    filter. On the phone, where the header is hidden, the cards get an «Ordenar por» select of the system with every
    column and direction, which follows the links of the server or orders in the browser.
    <x-ui.table caption="Firmas" :columns="$columns" sortable filterable>...</x-ui.table>
--}}
@use('App\Support\ListPage')
@use('Illuminate\Contracts\Pagination\LengthAwarePaginator')
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
    'countSingular' => null,
    'id' => null,
    'paginator' => null,
    'paginationLabel' => null,
    'sortable' => false,
    'sortLabel' => 'Ordenar por',
])
@php
    throw_if(blank($caption), InvalidArgumentException::class, 'The table component needs a caption.');
    throw_if(
        $paginator !== null && !$paginator instanceof LengthAwarePaginator,
        InvalidArgumentException::class,
        'The table paginates with a LengthAwarePaginator.',
    );
    $paged = $paginator !== null;
    $quickFilter = $filterable && !$paged;
    $searchable = false;
    $pageFooter = false;
    if ($paged) {
        // The query string of the list as its links carry it, without the page
        parse_str((string) parse_url($paginator->url(1), PHP_URL_QUERY), $listQuery);
        unset($listQuery[$paginator->getPageName()]);
        $term = is_string($listQuery[ListPage::SEARCH] ?? null) ? $listQuery[ListPage::SEARCH] : '';
        $kept = Arr::except($listQuery, [ListPage::SEARCH]);
        // The fragment of the paginator brings every link and the search back to the table
        $fragment = filled($paginator->fragment()) ? '#' . $paginator->fragment() : '';
        $listUrl = fn(array $query): string => $paginator->path() .
            ($query === [] ? '' : '?' . Arr::query($query)) .
            $fragment;
        $searchable = $filterable && ($paginator->total() > 0 || $term !== '');
        $sizes = $paginator->total() > min(ListPage::SIZES) ? ListPage::SIZES : [];
        $pageFooter = $paginator->hasPages() || $sizes !== [];
        $summary =
            $paginator->total() > 0
                ? 'De la ' . $paginator->firstItem() . ' a la ' . $paginator->lastItem() . ' de ' . $paginator->total()
                : null;
        $paginationLabel ??= 'Paginación de ' . $caption;
        $count = $paginator->total() . ' ' . ($paginator->total() === 1 ? $countSingular ?? $countLabel : $countLabel);
    }
    $columns = collect($columns)
        ->map(fn($column) => $column instanceof Arrayable ? $column->toArray() : $column)
        ->values()
        ->all();
    // Order: links of the server (ListSort) or buttons of the browser, and the select of the phone cards
    $hasRows = !isset($empty);
    $serverSorted = fn(array $column): bool => is_array($column['sort'] ?? null) && !empty($column['sort']['href']);
    $serverSort = collect($columns)->contains($serverSorted);
    $browserSort = $sortable && !$paged && !$serverSort && $hasRows;
    $browserSorted = fn(array $column): bool => $browserSort &&
        filled($column['label'] ?? null) &&
        empty($column['srOnly']) &&
        ($column['sortable'] ?? true) !== false;
    $sortOptions = [];
    foreach ($columns as $index => $column) {
        foreach (['asc' => 'ascendente', 'desc' => 'descendente'] as $way => $wayLabel) {
            if ($serverSorted($column) && !empty($column['sort'][$way])) {
                $sortOptions[] = [
                    'value' => $column['sort'][$way],
                    'label' => $column['label'] . ', ' . $wayLabel,
                    'selected' => ($column['sort']['dir'] ?? null) === $way,
                ];
            } elseif ($browserSorted($column)) {
                $sortOptions[] = [
                    'value' => $index . ':' . $way,
                    'label' => $column['label'] . ', ' . $wayLabel,
                    'selected' => ($column['sorted'] ?? null) === $way,
                ];
            }
        }
    }
    $phoneSort = $cards && $hasRows && $sortOptions !== [];
    $sortMode = $browserSort ? 'browser' : ($phoneSort ? 'server' : null);
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
    {{ $attributes->class(['glass overflow-hidden rounded-xl', $rowHeight])->merge(
        array_filter([
            'aria-label' => $caption,
            'id' => $id,
            'x-data' => $sortMode !== null ? 'tableSort' : null,
            'data-sort-mode' => $sortMode,
        ]),
    ) }}>
    @if (isset($toolbar) || $quickFilter || $searchable || $phoneSort)
        <div
            {{ $toolbarAttributes->class([$toolbarClasses, 'md:hidden' => !isset($toolbar) && !$quickFilter && !$searchable])->merge($quickFilter ? ['x-data' => 'tableFilter', 'data-count-label' => $countLabel] : []) }}>
            @isset($toolbar)
                {{ $toolbar }}
            @endisset
            @if ($searchable)
                <form role="search" method="get" action="{{ $paginator->path() . $fragment }}"
                    aria-label="{{ $filterPlaceholder }}"
                    class="flex min-w-0 flex-1 items-center gap-1.5 md:ml-auto md:max-w-80">
                    @foreach (ListPage::fields($kept) as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <input type="search" name="{{ ListPage::SEARCH }}" value="{{ $term }}" maxlength="100"
                        autocomplete="off" enterkeyhint="search" placeholder="{{ $filterPlaceholder }}"
                        aria-label="{{ $filterPlaceholder }}"
                        class="h-9 w-0 min-w-0 flex-1 rounded-full border border-field-edge bg-field px-4 text-control text-ink placeholder:text-ink-muted pointer-coarse:min-h-11">
                    <x-ui.button type="submit" icon-only icon="search" label="Buscar" size="sm" />
                    @if ($term !== '')
                        <x-ui.button icon-only icon="x" label="Quitar la búsqueda" :href="$listUrl($kept)"
                            variant="ghost" size="sm" />
                    @endif
                </form>
                <span class="text-sm text-ink-muted tabular-nums">{{ $count }}</span>
            @endif
            @if ($quickFilter)
                <input type="search" x-model="query" x-on:input.debounce.150ms="filter" autocomplete="off"
                    placeholder="{{ $filterPlaceholder }}" aria-label="{{ $filterPlaceholder }}"
                    class="h-9 w-0 min-w-0 flex-1 rounded-full border border-field-edge bg-field px-4 text-control text-ink placeholder:text-ink-muted md:ml-auto md:max-w-64 pointer-coarse:min-h-11">
                <span role="status" class="text-sm text-ink-muted tabular-nums" x-text="summary"></span>
            @endif
            @if ($phoneSort)
                {{-- Beside a search on the server it takes a row of its own, so that its buttons keep their size --}}
                <div @class([
                    'relative flex max-w-full min-w-0 items-center md:hidden',
                    'max-md:basis-full' => $searchable,
                ])>
                    <x-lucide-arrow-up-down aria-hidden="true"
                        class="pointer-events-none absolute left-3 size-4 text-ink-muted" />
                    <select data-table-sort x-on:change="choose" aria-label="{{ $sortLabel }}"
                        class="h-9 max-w-full min-w-0 cursor-pointer appearance-none truncate rounded-full border border-field-edge bg-field pr-9 pl-9 text-control text-ink pointer-coarse:min-h-11">
                        <option value="" disabled @selected(!collect($sortOptions)->contains('selected', true))>{{ $sortLabel }}</option>
                        @foreach ($sortOptions as $option)
                            <option value="{{ $option['value'] }}" @selected($option['selected'])>{{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <x-lucide-chevron-down aria-hidden="true"
                        class="pointer-events-none absolute right-3 size-4 text-ink-muted" />
                </div>
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
                        @foreach ($columns as $index => $column)
                            @php
                                $sort = $column['sort'] ?? null;
                                $linked = $serverSorted($column);
                                $button = !$linked && $browserSorted($column);
                                $dir = match (true) {
                                    $linked => $sort['dir'] ?? null,
                                    $button => $column['sorted'] ?? null,
                                    default => null,
                                };
                            @endphp
                            <th role="columnheader" scope="col" @class([
                                $headerClasses($column, $linked || $button),
                                'group/sort' => $button,
                            ])
                                @if ($button) data-label="{{ $column['label'] }}" @endif
                                @if (($linked || $button) && in_array($dir, ['asc', 'desc'], true)) aria-sort="{{ $dir === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                @if ($button)
                                    <button type="button" x-on:click="toggle" data-column="{{ $index }}"
                                        class="inline-flex min-h-6 cursor-pointer items-center gap-1 font-semibold tracking-wider text-ink uppercase pointer-coarse:min-h-11">
                                        {{ $column['label'] }}
                                        <x-lucide-arrow-up-down aria-hidden="true"
                                            class="size-3.5 opacity-55 group-aria-[sort]/sort:hidden" />
                                        <x-lucide-arrow-up aria-hidden="true"
                                            class="hidden size-3.5 group-aria-[sort=ascending]/sort:block" />
                                        <x-lucide-arrow-down aria-hidden="true"
                                            class="hidden size-3.5 group-aria-[sort=descending]/sort:block" />
                                    </button>
                                @elseif ($linked)
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
    @if (isset($footer) || $pageFooter)
        <div
            {{ (isset($footer) ? $footer->attributes : new \Illuminate\View\ComponentAttributeBag())->class(['flex flex-col items-start gap-2 border-t border-rule px-5 py-4 text-sm text-ink-muted md:flex-row md:items-center md:justify-between']) }}>
            @isset($footer)
                {{ $footer }}
            @endisset
            @if ($pageFooter)
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 md:ml-auto">
                    @if ($sizes !== [])
                        <div role="group" aria-label="Filas por página" class="flex items-center gap-1">
                            <span aria-hidden="true" class="mr-1">Por página</span>
                            @foreach ($sizes as $size)
                                <a href="{{ $listUrl(array_merge($listQuery, [ListPage::SIZE => $size])) }}"
                                    @if ($size === $paginator->perPage()) aria-current="true" @endif
                                    class="grid h-8 min-w-8 place-items-center rounded-full px-2 font-semibold text-ink-muted tabular-nums no-underline transition current:bg-brand-soft current:text-brand-soft-ink hover:bg-glass-2 hover:text-ink pointer-coarse:size-11">{{ $size }}</a>
                            @endforeach
                        </div>
                    @endif
                    <x-ui.pagination :paginator="$paginator" :label="$paginationLabel" :summary="$summary" />
                </div>
            @endif
        </div>
    @endif
</section>
