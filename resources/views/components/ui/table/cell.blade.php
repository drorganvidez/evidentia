{{--
    Cell of x-ui.table.row. label names the column in the phone cards, where the thead is hidden, with a text that
    only assistive technology reads and the quick filter skips (never in the title or action cells); primary is the
    title cell, whose links reach 44 px under a coarse pointer; meta is the secondary text, numeric the figures; end
    pushes the content to the right of the card; hide and actions disappear on the phone (hide only what the detail
    behind the row link shows again); header prints a th scope="row".
    <x-ui.table.cell label="Evidencia" primary><a href="...">{{ $title }}</a></x-ui.table.cell>
    <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="1.75" /></x-ui.table.cell>
    <x-ui.table.cell actions><x-ui.button icon-only icon="eye" label="Abrir" href="..." dim /></x-ui.table.cell>
    The tag is printed from PHP because blade-formatter does not follow dynamic tags.
--}}
@props([
    'label' => null,
    'numeric' => false,
    'primary' => false,
    'meta' => false,
    'end' => false,
    'hide' => false,
    'full' => false,
    'actions' => false,
    'header' => false,
])
@php
    $tag = $header ? 'th' : 'td';
    $attrs = $attributes
        ->class([
            'h-(--row-h) px-5 py-4 align-middle in-data-cards:block in-data-cards:h-auto in-data-cards:p-0 md:table-cell md:h-(--row-h) md:px-5 md:py-4 md:align-middle',
            'text-right font-mono tabular-nums in-data-cards:text-left md:text-right' => $numeric,
            'in-data-cards:col-span-2 [&_a]:font-semibold [&_a]:tracking-tight [&_a]:text-ink [&_a:hover]:text-brand-ink pointer-coarse:[&_a]:inline-flex pointer-coarse:[&_a]:min-h-11 pointer-coarse:[&_a]:items-center' => $primary,
            'text-sm text-ink-muted' => $meta,
            'in-data-cards:justify-self-end' => $end,
            'in-data-cards:max-md:hidden' => $hide || $actions,
            'in-data-cards:col-span-full' => $full,
            'w-[1%] whitespace-nowrap' => $actions,
            'text-left font-semibold' => $header,
        ])
        ->merge(
            array_filter([
                'role' => $header ? 'rowheader' : 'cell',
                'scope' => $header ? 'row' : null,
            ]),
        );
    $columnName = filled($label) && !$primary && !$actions ? $label : null;
@endphp
{!! '<' . $tag . ' ' . $attrs . '>' !!}@if ($columnName !== null)
    <span class="sr-only hidden in-data-cards:max-md:inline" data-filter-ignore>{{ $columnName }}: </span>
@endif{{ $slot }}{!! '</' . $tag . '>' !!}
