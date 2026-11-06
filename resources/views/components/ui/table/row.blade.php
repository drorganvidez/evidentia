{{--
    Row of x-ui.table: a card on the phone, a table row from md. With href the whole row follows the link on click
    (a pointer enhancement only: the link inside the primary cell is the keyboard path, so the row takes no tabindex).
    <x-ui.table.row :href="route('evidence.view', $evidence)" :search="$evidence->title">...</x-ui.table.row>
    <x-ui.table.row flag="Firma fuera de plazo">...</x-ui.table.row>
--}}
@props(['href' => null, 'flag' => null, 'search' => null])
@php
    $flagged = $flag !== null && $flag !== '';
    $html = $slot->toHtml();
    // A tr takes no text of its own, so the notice of a flagged row opens its first cell; the callback keeps
    // a $1 or a backslash of the flag as text instead of a back reference
    if ($flagged) {
        $notice = '<span class="sr-only">Señalada, ' . e($flag) . '. </span>';
        $html =
            preg_replace_callback('/<t[dh]\b[^>]*>/i', fn(array $match): string => $match[0] . $notice, $html, 1) ??
            $html;
    }
@endphp
<tr role="row"
    {{ $attributes->class([
            'group border-t border-rule transition first:border-t-0 hover:bg-glass-2 in-data-cards:grid in-data-cards:grid-cols-[minmax(0,1fr)_auto] in-data-cards:items-center in-data-cards:gap-x-3 in-data-cards:gap-y-2 in-data-cards:p-4 md:table-row md:p-0',
            'cursor-pointer' => $href !== null,
            '[&>:first-child]:inset-shadow-[4px_0_0_var(--pending)] in-data-cards:max-md:[&>:first-child]:pl-3' => $flagged,
        ])->merge(array_filter(['data-href' => $href, 'data-search' => $search])) }}
    @if ($href !== null) x-data="rowLink" x-on:click="go" x-on:auxclick="go" @endif>
    {!! $html !!}
</tr>
